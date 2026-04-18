<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Services\PayTechService;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function __construct(private PayTechService $payTechService) {}

    /**
     * Page de simulation du paiement PayTech
     */
    public function pageSimulation(string $token)
    {
        $data = json_decode(base64_decode($token), true);
        abort_if(!$data, 400, 'Token invalide');

        $paiement = Paiement::with('demande.user')->findOrFail($data['paiement_id']);
        $demande  = $paiement->demande;

        // Vérifier que la demande appartient à l'utilisateur connecté
        abort_unless($demande->user_id === auth()->id(), 403);

        return view('user.paiement.simulation', compact('paiement', 'demande'));
    }

    /**
     * Confirmer paiement (mode simulation)
     */
    public function confirmerSimulation(Request $request)
    {
        $request->validate(['paiement_id' => 'required|integer|exists:paiements,id']);

        $paiement = Paiement::with('demande')->findOrFail($request->paiement_id);
        abort_unless($paiement->demande->user_id === auth()->id(), 403);

        $ok = $this->payTechService->confirmerSimulation($paiement->id);

        if ($ok) {
            return redirect()
                ->route('paiement.succes', $paiement->reference_paiement)
                ->with('success', 'Paiement effectué avec succès !');
        }

        return back()->with('error', 'Erreur lors du paiement.');
    }

    /**
     * Page de succès après paiement
     */
    public function succes(string $reference)
    {
        $paiement = Paiement::with('demande')
            ->where('reference_paiement', $reference)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('user.paiement.succes', compact('paiement'));
    }

    /**
     * Page d'annulation
     */
    public function annuler(string $reference)
    {
        $paiement = Paiement::where('reference_paiement', $reference)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $paiement->update(['statut' => 'annule']);

        return redirect()
            ->route('demandes.show', $paiement->demande_id)
            ->with('error', 'Paiement annulé. Vous pouvez réessayer depuis votre demande.');
    }

    /**
     * Webhook IPN PayTech (hors authentification)
     */
    public function ipn(Request $request)
    {
        $ok = $this->payTechService->traiterIPN($request->all());
        return response()->json(['status' => $ok ? 'ok' : 'error'], $ok ? 200 : 400);
    }
}
