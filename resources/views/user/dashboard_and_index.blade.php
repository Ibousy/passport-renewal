{{-- ════════════════════════════════════════════════════════
    resources/views/user/dashboard.blade.php
════════════════════════════════════════════════════════ --}}
@extends('layouts.app')
@section('title', 'Mon tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
@php
    $user     = auth()->user();
    $demandes = $user->demandes()->latest()->get();
    $total    = $demandes->count();
    $enCours  = $demandes->whereNotIn('statut', ['validee','rejetee','delivre','brouillon'])->count();
    $validees = $demandes->where('statut', 'validee')->count();
    $brouillons = $demandes->where('statut', 'brouillon')->count();
@endphp

{{-- Bienvenue --}}
<div class="rounded-3 p-4 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3"
     style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;">
    <div>
        <h2 class="mb-1" style="font-family:'Playfair Display',serif;font-size:1.5rem;">
            Bonjour, {{ $user->prenom }} 👋
        </h2>
        <p class="mb-0" style="opacity:.85;font-size:.9rem;">
            Bienvenue sur votre espace de renouvellement de passeport.
        </p>
    </div>
    <a href="{{ route('demandes.create') }}" class="btn-accent btn">
        <i class="bi bi-plus-circle me-2"></i>Nouvelle demande
    </a>
</div>

{{-- Statistiques --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Demandes totales',  $total,      'bi-collection',      '#dbeafe', '#1d4ed8'],
        ['En cours',          $enCours,    'bi-hourglass-split', '#fef3c7', '#d97706'],
        ['Validées',          $validees,   'bi-check-circle',    '#dcfce7', '#16a34a'],
        ['Brouillons',        $brouillons, 'bi-floppy',          '#f3e8ff', '#7c3aed'],
    ] as [$label, $val, $icon, $bg, $color])
    <div class="col-6 col-lg-3">
        <div class="card p-4 text-center h-100">
            <div style="width:48px;height:48px;border-radius:14px;background:{{ $bg }};
                 display:flex;align-items:center;justify-content:center;font-size:1.4rem;
                 color:{{ $color }};margin:0 auto 12px;">
                <i class="bi {{ $icon }}"></i>
            </div>
            <div style="font-size:2rem;font-weight:800;color:var(--primary);">{{ $val }}</div>
            <div class="text-muted" style="font-size:.82rem;">{{ $label }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Mes demandes récentes --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-earmark-person me-2"></i>Mes demandes récentes</span>
        <a href="{{ route('demandes.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
            Tout voir
        </a>
    </div>
    <div class="card-body p-0">
        @forelse($demandes->take(5) as $demande)
        <div class="p-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <code style="font-size:.8rem;background:#f1f5f9;padding:3px 8px;border-radius:6px;color:var(--primary);">
                    {{ $demande->numero_demande }}
                </code>
                <div class="mt-1 text-muted" style="font-size:.82rem;">
                    {{ ucfirst($demande->type_passeport) }} ·
                    {{ ucfirst(str_replace('_', ' ', $demande->motif_renouvellement)) }} ·
                    {{ $demande->created_at->format('d/m/Y') }}
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge badge-statut bg-{{ $demande->statut_color }}">{{ $demande->statut_label }}</span>
                <a href="{{ route('demandes.show', $demande) }}"
                   class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                    Voir <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <i class="bi bi-file-earmark-plus fs-2 d-block mb-3 text-muted opacity-50"></i>
            <div class="text-muted mb-3">Aucune demande pour le moment.</div>
            <a href="{{ route('demandes.create') }}" class="btn-primary-custom btn">
                Commencer une demande
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection


{{-- ════════════════════════════════════════════════════════
    resources/views/user/demandes/index.blade.php
════════════════════════════════════════════════════════ --}}
@extends('layouts.app')
@section('title', 'Mes demandes')
@section('page-title', 'Mes demandes')

@section('content')

@php
    $demandesTotal = $demandes instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ? $demandes->total()
        : $demandes->count();
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="text-muted" style="font-size:.87rem;">
        {{ $demandesTotal }} demande(s) au total
    </div>
    <a href="{{ route('demandes.create') }}" class="btn-primary-custom btn">
        <i class="bi bi-plus me-2"></i>Nouvelle demande
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        @forelse($demandes as $demande)
        <div class="p-4 border-bottom">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <code style="font-size:.85rem;background:#f1f5f9;padding:4px 10px;border-radius:8px;color:var(--primary);">
                            {{ $demande->numero_demande }}
                        </code>
                        @if($demande->urgence)
                            <span class="badge bg-warning text-dark"><i class="bi bi-lightning me-1"></i>Urgent</span>
                        @endif
                    </div>
                    <div style="font-size:.85rem;color:#64748b;">
                        <span class="badge bg-light text-dark border me-1">{{ ucfirst($demande->type_passeport) }}</span>
                        {{ ucfirst(str_replace('_', ' ', $demande->motif_renouvellement)) }}
                        · <strong>{{ $demande->montant_formate }}</strong>
                        · {{ $demande->created_at->format('d/m/Y') }}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-statut bg-{{ $demande->statut_color }} fs-6">
                        {{ $demande->statut_label }}
                    </span>
                    <a href="{{ route('demandes.show', $demande) }}"
                       class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
                        <i class="bi bi-eye me-1"></i>Détails
                    </a>
                    @if($demande->statut === 'en_attente_paiement')
                    <form method="POST" action="{{ route('demandes.paiement', $demande) }}">
                        @csrf
                        <button class="btn btn-sm btn-accent" style="border-radius:8px;">
                            <i class="bi bi-credit-card me-1"></i>Payer
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <i class="bi bi-inbox fs-2 d-block mb-3 text-muted opacity-25"></i>
            <div class="text-muted">Aucune demande pour l'instant.</div>
            <a href="{{ route('demandes.create') }}" class="btn-primary-custom btn mt-3">
                Faire une demande
            </a>
        </div>
        @endforelse
    </div>
    @if($demandes instanceof \Illuminate\Contracts\Pagination\Paginator || $demandes instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
    <div class="p-3 border-top">
        {{ $demandes->links() }}
    </div>
    @endif
</div>
@endsection
