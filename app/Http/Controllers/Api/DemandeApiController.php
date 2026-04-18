<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DemandeApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $demandes = $request->user()
            ->demandes()
            ->with('paiementSucces')
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => $demandes->map(fn($d) => $this->formatDemande($d)),
            'meta' => [
                'current_page' => $demandes->currentPage(),
                'last_page'    => $demandes->lastPage(),
                'total'        => $demandes->total(),
            ],
        ]);
    }

    public function show(Demande $demande): JsonResponse
    {
        abort_unless($demande->user_id === auth()->id(), 403);

        $demande->load(['documents', 'paiements', 'notifications']);

        return response()->json([
            'data' => [
                ...$this->formatDemande($demande),
                'documents'     => $demande->documents->map(fn($d) => [
                    'id'            => $d->id,
                    'type'          => $d->type_document,
                    'type_label'    => \App\Models\Document::TYPES_LABELS[$d->type_document] ?? $d->type_document,
                    'nom_original'  => $d->nom_original,
                    'taille'        => $d->taille_formatee,
                    'statut'        => $d->statut,
                    'url'           => $d->url,
                ]),
                'paiements'     => $demande->paiements->map(fn($p) => [
                    'id'         => $p->id,
                    'reference'  => $p->reference_paiement,
                    'montant'    => $p->montant_formate,
                    'statut'     => $p->statut,
                    'date'       => $p->date_paiement?->toDateTimeString(),
                ]),
            ],
        ]);
    }

    public function statut(Demande $demande): JsonResponse
    {
        abort_unless($demande->user_id === auth()->id(), 403);

        return response()->json([
            'numero_demande' => $demande->numero_demande,
            'statut'         => $demande->statut,
            'statut_label'   => $demande->statut_label,
            'statut_color'   => $demande->statut_color,
            'derniere_mise_a_jour' => $demande->updated_at->toDateTimeString(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type_passeport'       => 'required|in:ordinaire,diplomatique,service',
            'motif_renouvellement' => 'required|in:expiration,perte,vol,deterioration,changement_etat_civil,autre',
            'nom_complet'          => 'required|string|max:120',
            'date_naissance'       => 'required|date',
            'lieu_naissance'       => 'required|string|max:100',
            'nationalite'          => 'required|string|max:60',
            'cin'                  => 'required|string|max:20',
            'adresse_residence'    => 'required|string|max:300',
            'ville'                => 'required|string|max:100',
            'urgence'              => 'boolean',
        ]);

        $urgence = $request->boolean('urgence');
        $demande = Demande::create([
            ...$validated,
            'user_id'         => auth()->id(),
            'statut'          => 'en_attente_paiement',
            'urgence'         => $urgence,
            'montant_total'   => Demande::calculerMontant($validated['type_passeport'], $urgence),
            'date_soumission' => now(),
        ]);

        return response()->json([
            'message' => 'Demande créée avec succès.',
            'data'    => $this->formatDemande($demande),
        ], 201);
    }

    private function formatDemande(Demande $d): array
    {
        return [
            'id'              => $d->id,
            'numero_demande'  => $d->numero_demande,
            'statut'          => $d->statut,
            'statut_label'    => $d->statut_label,
            'statut_color'    => $d->statut_color,
            'type_passeport'  => $d->type_passeport,
            'motif'           => $d->motif_renouvellement,
            'montant'         => (float) $d->montant_total,
            'montant_formate' => $d->montant_formate,
            'urgence'         => $d->urgence,
            'est_payee'       => $d->estPayee(),
            'date_soumission' => $d->date_soumission?->toDateString(),
            'date_validation' => $d->date_validation?->toDateString(),
            'date_rdv'        => $d->date_rdv?->toDateString(),
            'created_at'      => $d->created_at->toDateTimeString(),
        ];
    }
}

