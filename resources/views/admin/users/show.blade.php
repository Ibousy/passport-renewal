@extends('layouts.app')
@section('title', 'Utilisateur ' . $user->nom_complet)
@section('page-title', 'Détail utilisateur')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">← Retour à la liste</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="mb-3">{{ $user->nom_complet }}</h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted">Email</div>
                        <div>{{ $user->email }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">Téléphone</div>
                        <div>{{ $user->telephone ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">CIN</div>
                        <div>{{ $user->cin ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">Nationalité</div>
                        <div>{{ $user->nationalite ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">Statut</div>
                        <div>{{ $user->is_active ? 'Actif' : 'Désactivé' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">Inscrit le</div>
                        <div>{{ $user->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Demandes récentes</div>
            <div class="card-body p-0">
                @forelse($user->demandes as $demande)
                <div class="p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <div class="fw-bold">{{ $demande->numero_demande }}</div>
                            <div class="text-muted" style="font-size:.9rem;">
                                {{ ucfirst($demande->type_passeport) }} · {{ $demande->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                        <span class="badge bg-{{ $demande->statut_color }}">{{ $demande->statut_label }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">Aucune demande récente.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
