<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\User;
use App\Models\Paiement;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_demandes'       => Demande::count(),
            'demandes_en_attente'  => Demande::whereIn('statut', ['soumise', 'en_attente_paiement', 'payee'])->count(),
            'demandes_en_cours'    => Demande::where('statut', 'en_cours_traitement')->count(),
            'demandes_validees'    => Demande::where('statut', 'validee')->count(),
            'demandes_rejetees'    => Demande::where('statut', 'rejetee')->count(),
            'total_utilisateurs'   => User::where('role', 'user')->count(),
            'nouveaux_ce_mois'     => User::where('role', 'user')->whereMonth('created_at', now()->month)->count(),
            'revenus_total'        => Paiement::where('statut', 'succes')->sum('montant'),
            'revenus_ce_mois'      => Paiement::where('statut', 'succes')->whereMonth('created_at', now()->month)->sum('montant'),
            'demandes_urgentes'    => Demande::where('urgence', true)->whereNotIn('statut', ['validee', 'rejetee', 'delivre'])->count(),
        ];

        $evolutionDemandes = Demande::selectRaw("strftime('%m', created_at) as mois, COUNT(*) as total")
            ->whereYear('created_at', now()->year)
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois');

        $repartitionStatuts = Demande::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $dernieresDemandes = Demande::with('user')
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'evolutionDemandes', 'repartitionStatuts', 'dernieresDemandes'
        ));
    }
}

