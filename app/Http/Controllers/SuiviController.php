<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use Illuminate\Http\Request;

class SuiviController extends Controller
{
    /**
     * Affiche le formulaire de suivi public (GET /suivi)
     */
    public function index()
    {
        return view('suivi');
    }

    /**
     * Recherche une demande par son code (POST /suivi)
     */
    public function chercher(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:30',
        ], [
            'code.required' => 'Veuillez saisir un code de demande.',
        ]);

        $code    = strtoupper(trim($request->input('code')));
        $demande = Demande::where('numero_demande', $code)
                          ->whereNotIn('statut', ['brouillon'])
                          ->first();

        if (! $demande) {
            return back()
                ->withInput()
                ->with('suivi_error', "Aucune demande trouvée pour le code « {$code} ».");
        }

        // Infos publiques uniquement (pas d'informations sensibles complètes)
        return view('suivi', compact('demande'));
    }
}
