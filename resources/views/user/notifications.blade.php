@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1">Notifications</h2>
            <p class="text-muted mb-0">Toutes vos notifications récentes.</p>
        </div>
        <span class="text-muted" style="font-size:.82rem;">
            <i class="bi bi-check2-all me-1"></i>Toutes marquées comme lues
        </span>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @forelse($notifications as $notification)
        @php
            $icones = [
                'demande_soumise'    => ['bi-check-circle-fill',        '#16a34a', '#f0fdf4', '#dcfce7'],
                'paiement_recu'      => ['bi-credit-card-fill',         '#0369a1', '#eff6ff', '#dbeafe'],
                'demande_en_cours'   => ['bi-arrow-repeat',             '#7c3aed', '#faf5ff', '#ede9fe'],
                'documents_manquants'=> ['bi-exclamation-triangle-fill','#d97706', '#fffbeb', '#fef3c7'],
                'demande_validee'    => ['bi-patch-check-fill',         '#059669', '#f0fdf4', '#a7f3d0'],
                'demande_rejetee'    => ['bi-x-circle-fill',            '#dc2626', '#fef2f2', '#fecaca'],
                'passeport_pret'     => ['bi-passport-fill',            '#1a3a6b', '#eff6ff', '#bfdbfe'],
                'rdv_confirme'       => ['bi-calendar-check-fill',      '#0891b2', '#ecfeff', '#a5f3fc'],
                'systeme'            => ['bi-info-circle-fill',         '#64748b', '#f8fafc', '#e2e8f0'],
            ];
            [$ico, $color, $bg, $border] = $icones[$notification->type] ?? $icones['systeme'];
        @endphp
            <div class="p-4 border-bottom">
                <div class="d-flex gap-3 align-items-start">
                    {{-- Icône colorée --}}
                    <div style="width:42px;height:42px;border-radius:12px;background:{{ $bg }};
                         border:1.5px solid {{ $border }};flex-shrink:0;
                         display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                        <i class="bi {{ $ico }}" style="color:{{ $color }};"></i>
                    </div>
                    {{-- Contenu --}}
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                            <div class="fw-600" style="font-size:.92rem;color:#1e293b;">
                                {{ $notification->titre }}
                            </div>
                            <div class="text-muted flex-shrink-0" style="font-size:.78rem;">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="mt-1" style="font-size:.87rem;color:#475569;line-height:1.6;">
                            {{ $notification->message }}
                        </div>
                        @if($notification->demande_id)
                        <div class="mt-2">
                            <a href="{{ route('demandes.show', $notification->demande_id) }}"
                               class="btn btn-sm" style="background:{{ $bg }};color:{{ $color }};border:1px solid {{ $border }};border-radius:8px;font-size:.78rem;">
                                <i class="bi bi-arrow-right me-1"></i>Voir la demande
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash fs-2 d-block mb-3 opacity-25"></i>
                <div>Aucune notification pour le moment.</div>
                <div style="font-size:.83rem;margin-top:6px;">Vous serez notifié à chaque étape du traitement de vos demandes.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
