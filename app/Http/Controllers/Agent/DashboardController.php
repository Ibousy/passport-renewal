<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Demande;

class DashboardController extends Controller
{
    public function index()
    {
        $agentId = auth()->id();

        $stats = [
            'en_attente'  => Demande::whereIn('statut', ['soumise', 'en_attente_paiement', 'payee'])->count(),
            'en_cours'    => Demande::where('statut', 'en_cours_traitement')->where('traite_par', $agentId)->count(),
            'traitees'    => Demande::whereIn('statut', ['validee', 'rejetee', 'passeport_pret', 'delivre'])
                                    ->where('traite_par', $agentId)->count(),
            'total_today' => Demande::where('traite_par', $agentId)
                                    ->whereDate('date_traitement', today())->count(),
        ];

        $demandes_recentes = Demande::with(['user'])
            ->whereIn('statut', ['soumise', 'payee', 'en_cours_traitement'])
            ->latest()
            ->take(10)
            ->get();

        return view('agent.dashboard', compact('stats', 'demandes_recentes'));
    }
}
