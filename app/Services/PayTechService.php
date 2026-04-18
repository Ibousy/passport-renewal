<?php

namespace App\Services;

use App\Models\Demande;
use App\Models\Paiement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service d'intégration PayTech
 *
 * PayTech est une solution de paiement africaine.
 * En mode simulation (PAYTECH_SIMULATION=true dans .env),
 * tous les paiements sont approuvés automatiquement.
 */
class PayTechService
{
    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl;
    private bool   $simulation;

    public function __construct()
    {
        $this->apiKey     = config('paytech.api_key', '');
        $this->apiSecret  = config('paytech.api_secret', '');
        $this->baseUrl    = config('paytech.base_url', 'https://paytech.sn/api');
        $this->simulation = config('paytech.simulation', true);
    }

    // ─────────────────────────────────────────────────────────────────
    // INITIER UN PAIEMENT
    // ─────────────────────────────────────────────────────────────────

    public function initierPaiement(Demande $demande): array
    {
        $reference = Paiement::genererReference();

        // Créer l'enregistrement paiement en BDD
        $paiement = Paiement::create([
            'demande_id'         => $demande->id,
            'user_id'            => $demande->user_id,
            'reference_paiement' => $reference,
            'montant'            => $demande->montant_total,
            'devise'             => 'XOF',
            'methode'            => 'paytech',
            'statut'             => 'initie',
            'ip_paiement'        => request()->ip(),
        ]);

        if ($this->simulation) {
            return $this->simulerPaiement($paiement, $demande);
        }

        return $this->appelerApiPayTech($paiement, $demande);
    }

    // ─────────────────────────────────────────────────────────────────
    // MODE SIMULATION
    // ─────────────────────────────────────────────────────────────────

    private function simulerPaiement(Paiement $paiement, Demande $demande): array
    {
        // Retourne une URL de paiement simulée
        $token = base64_encode(json_encode([
            'paiement_id' => $paiement->id,
            'reference'   => $paiement->reference_paiement,
            'montant'     => $paiement->montant,
            'ts'          => time(),
        ]));

        return [
            'success'     => true,
            'paiement_id' => $paiement->id,
            'reference'   => $paiement->reference_paiement,
            'redirect_url' => route('paiement.simulation', ['token' => $token]),
            'simulation'  => true,
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // APPEL API RÉELLE PAYTECH
    // ─────────────────────────────────────────────────────────────────

    private function appelerApiPayTech(Paiement $paiement, Demande $demande): array
    {
        try {
            $payload = [
                'item_name'    => "Renouvellement passeport - {$demande->numero_demande}",
                'item_price'   => (int) $paiement->montant,
                'currency'     => 'XOF',
                'ref_command'  => $paiement->reference_paiement,
                'command_name' => "Demande {$demande->numero_demande}",
                'env'          => 'prod',
                'ipn_url'      => route('paiement.ipn'),
                'success_url'  => route('paiement.succes', $paiement->reference_paiement),
                'cancel_url'   => route('paiement.annuler', $paiement->reference_paiement),
                'custom_field' => json_encode([
                    'demande_id' => $demande->id,
                    'user_id'    => $demande->user_id,
                ]),
            ];

            $response = Http::withHeaders([
                'API_KEY'    => $this->apiKey,
                'API_SECRET' => $this->apiSecret,
            ])->post("{$this->baseUrl}/payment/request-payment", $payload);

            if ($response->successful()) {
                $data = $response->json();

                $paiement->update([
                    'transaction_id'   => $data['token'] ?? null,
                    'reponse_gateway'  => $data,
                ]);

                return [
                    'success'      => true,
                    'paiement_id'  => $paiement->id,
                    'reference'    => $paiement->reference_paiement,
                    'redirect_url' => $data['redirect_url'] ?? '',
                ];
            }

            Log::error('PayTech error', ['response' => $response->body()]);
            return ['success' => false, 'message' => 'Erreur de connexion au service de paiement'];

        } catch (\Exception $e) {
            Log::error('PayTech exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Service de paiement indisponible'];
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // VÉRIFIER STATUT (IPN Webhook)
    // ─────────────────────────────────────────────────────────────────

    public function traiterIPN(array $data): bool
    {
        try {
            $reference = $data['ref_command'] ?? null;
            if (!$reference) return false;

            $paiement = Paiement::where('reference_paiement', $reference)->first();
            if (!$paiement) return false;

            // Vérifier la signature HMAC
            if (!$this->simulation && !$this->verifierSignature($data)) {
                Log::warning('PayTech IPN: signature invalide', ['ref' => $reference]);
                return false;
            }

            $statut = ($data['type_event'] === 'sale_complete') ? 'succes' : 'echec';

            $paiement->update([
                'statut'           => $statut,
                'transaction_id'   => $data['payment_method'] ?? $paiement->transaction_id,
                'reponse_gateway'  => $data,
                'date_paiement'    => now(),
            ]);

            // Mettre à jour le statut de la demande
            if ($statut === 'succes') {
                $paiement->demande->update(['statut' => 'payee']);

                // Envoyer notification
                app(NotificationService::class)->envoyer(
                    $paiement->demande,
                    'paiement_recu',
                    'Paiement confirmé',
                    "Votre paiement de {$paiement->montant_formate} a été reçu pour la demande {$paiement->demande->numero_demande}."
                );
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Erreur traitement IPN', ['message' => $e->getMessage()]);
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // CONFIRMER PAIEMENT SIMULATION
    // ─────────────────────────────────────────────────────────────────

    public function confirmerSimulation(int $paiementId): bool
    {
        $paiement = Paiement::find($paiementId);
        if (!$paiement || !$this->simulation) return false;

        $paiement->update([
            'statut'          => 'succes',
            'transaction_id'  => 'SIM-' . strtoupper(uniqid()),
            'date_paiement'   => now(),
            'reponse_gateway' => ['simulation' => true, 'confirmed_at' => now()->toIso8601String()],
        ]);

        $paiement->demande->update(['statut' => 'payee']);

        app(NotificationService::class)->envoyer(
            $paiement->demande,
            'paiement_recu',
            'Paiement confirmé',
            "Votre paiement de {$paiement->montant_formate} a été reçu."
        );

        return true;
    }

    private function verifierSignature(array $data): bool
    {
        $expectedSig = hash_hmac('sha256', $data['ref_command'], $this->apiSecret);
        return hash_equals($expectedSig, $data['signature'] ?? '');
    }
}
