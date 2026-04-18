@extends('layouts.app')
@section('title', 'Tableau de bord Agent')
@section('page-title', 'Tableau de bord Agent')

@section('content')

{{-- Stats ─────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#0369a1,#0284c7);">
            <div class="stat-icon"><i class="bi bi-inbox"></i></div>
            <div class="stat-number">{{ $stats['en_attente'] }}</div>
            <div class="stat-label">En attente de traitement</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#7c3aed,#8b5cf6);">
            <div class="stat-icon"><i class="bi bi-arrow-repeat"></i></div>
            <div class="stat-number">{{ $stats['en_cours'] }}</div>
            <div class="stat-label">En cours (mes dossiers)</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#059669,#10b981);">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-number">{{ $stats['traitees'] }}</div>
            <div class="stat-label">Demandes traitées (total)</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#d97706,#f59e0b);">
            <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
            <div class="stat-number">{{ $stats['total_today'] }}</div>
            <div class="stat-label">Traitées aujourd'hui</div>
        </div>
    </div>
</div>

{{-- Demandes récentes ────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-collection me-2"></i>Demandes à traiter</span>
        <a href="{{ route('admin.demandes.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.82rem;">
            Voir tout
        </a>
    </div>
    <div class="card-body p-0">
        @forelse($demandes_recentes as $demande)
        <div class="p-3 border-bottom d-flex align-items-center gap-3">
            <div style="width:36px;height:36px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-file-earmark-text" style="color:#0369a1;"></i>
            </div>
            <div class="flex-grow-1 min-width-0">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
                    <span class="fw-600" style="font-size:.9rem;">{{ $demande->numero_demande }}</span>
                    @php
                        $badgeColors = [
                            'soumise'             => '#0369a1',
                            'payee'               => '#059669',
                            'en_cours_traitement' => '#7c3aed',
                        ];
                        $bc = $badgeColors[$demande->statut] ?? '#64748b';
                    @endphp
                    <span class="badge" style="background:{{ $bc }}20;color:{{ $bc }};font-size:.72rem;border-radius:8px;padding:3px 9px;">
                        {{ $demande->statut_label ?? $demande->statut }}
                    </span>
                </div>
                <div class="text-muted" style="font-size:.8rem;">
                    {{ $demande->nom_complet }} · {{ $demande->type_passeport ?? '' }}
                    @if($demande->urgence)<span class="text-warning fw-600 ms-1"><i class="bi bi-exclamation-triangle-fill"></i> Urgent</span>@endif
                </div>
            </div>
            <a href="{{ route('admin.demandes.show', $demande) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.8rem;white-space:nowrap;">
                Traiter <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>
            Aucune demande en attente.
        </div>
        @endforelse
    </div>
</div>

@endsection
