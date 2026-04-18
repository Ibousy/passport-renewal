@extends('layouts.app')
@section('title', 'Mes demandes')
@section('page-title', 'Mes demandes')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="text-muted" style="font-size:.87rem;">
        {{ $demandes->total() }} demande(s) au total
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
    @if($demandes->hasPages())
    <div class="p-3 border-top">
        {{ $demandes->links() }}
    </div>
    @endif
</div>
@endsection
