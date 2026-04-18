<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Document;
use App\Models\User;
use App\Models\Paiement;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// Admin Dashboard Controller
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

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

        // Ã‰volution des demandes sur 6 mois
        $evolutionDemandes = Demande::selectRaw('MONTH(created_at) as mois, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois');

        // RÃ©partition par statut
        $repartitionStatuts = Demande::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        // DerniÃ¨res demandes
        $dernieresDemandes = Demande::with('user')
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'evolutionDemandes', 'repartitionStatuts', 'dernieresDemandes'
        ));
    }
}


// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// Admin Demande Controller
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

class DemandeAdminController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $query = Demande::with(['user', 'paiementSucces'])->latest();

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('type')) {
            $query->where('type_passeport', $request->type);
        }
        if ($request->filled('urgence')) {
            $query->where('urgence', $request->urgence === '1');
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_demande', 'like', "%{$search}%")
                  ->orWhere('nom_complet', 'like', "%{$search}%")
                  ->orWhere('cin', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('email', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $demandes = $query->paginate(20)->withQueryString();

        return view('admin.demandes.index', compact('demandes'));
    }

    public function show(Demande $demande)
    {
        $demande->load(['user', 'documents', 'paiements', 'notifications', 'traitePar']);
        return view('admin.demandes.show', compact('demande'));
    }

    public function changerStatut(Request $request, Demande $demande)
    {
        $request->validate([
            'statut'             => 'required|in:' . implode(',', array_keys(Demande::STATUTS_LABELS)),
            'commentaire_admin'  => 'nullable|string|max:1000',
            'motif_rejet'        => 'required_if:statut,rejetee|nullable|string|max:500',
            'date_rdv'           => 'nullable|date|after:today',
        ]);

        $ancienStatut = $demande->statut;

        $demande->update([
            'statut'            => $request->statut,
            'commentaire_admin' => $request->commentaire_admin,
            'motif_rejet'       => $request->motif_rejet,
            'date_rdv'          => $request->date_rdv,
            'traite_par'        => auth()->id(),
            'date_traitement'   => now(),
            'date_validation'   => in_array($request->statut, ['validee']) ? now() : $demande->date_validation,
        ]);

        $this->notificationService->notifierChangementStatut($demande, $ancienStatut);

        return back()->with('success', "Statut mis Ã  jour : {$demande->statut_label}");
    }

    public function validerDocument(Document $document)
    {
        $document->update(['statut' => 'valide']);
        return back()->with('success', 'Document validÃ©.');
    }

    public function rejeterDocument(Request $request, Document $document)
    {
        $request->validate(['commentaire' => 'required|string|max:300']);
        $document->update(['statut' => 'rejete', 'commentaire' => $request->commentaire]);
        return back()->with('success', 'Document rejetÃ©.');
    }

    public function telechargerDocument(Document $document)
    {
        abort_unless(Storage::disk('private')->exists($document->chemin_fichier), 404);

        return Storage::disk('private')->download(
            $document->chemin_fichier,
            $document->nom_original
        );
    }

    public function exporterPDF(Demande $demande)
    {
        $demande->load(['user', 'documents', 'paiementSucces']);
        // En production : utiliser barryvdh/laravel-dompdf
        $pdf = view('admin.demandes.pdf', compact('demande'));
        return response($pdf, 200)->header('Content-Type', 'text/html');
    }
}


// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// Admin User Controller
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cin', 'like', "%{$search}%");
            });
        }

        if ($request->filled('actif')) {
            $query->where('is_active', $request->actif === '1');
        }

        $users = $query->withCount('demandes')->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['demandes' => fn($q) => $q->latest()->take(10)]);
        return view('admin.users.show', compact('user'));
    }

    public function toggleActif(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $etat = $user->is_active ? 'activÃ©' : 'dÃ©sactivÃ©';
        return back()->with('success', "Compte {$etat} avec succÃ¨s.");
    }
}

