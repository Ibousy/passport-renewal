@extends('layouts.app')
@section('title', 'Gestion des Agents')
@section('page-title', 'Agents')

@section('content')

<div class="row g-4">

    {{-- ── Créer un agent ─────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus me-2"></i>Créer un agent
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.agents.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror"
                               value="{{ old('prenom') }}" required>
                        @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                               value="{{ old('nom') }}" required>
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control @error('telephone') is-invalid @enderror"
                               value="{{ old('telephone') }}" placeholder="+221 7X XXX XX XX">
                        @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Minimum 8 caractères, avec des lettres et des chiffres.</div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="bi bi-person-plus me-2"></i>Créer l'agent
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Liste des agents ────────────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Agents ({{ $agents->total() }})</span>
            </div>
            <div class="card-body p-0">
                @forelse($agents as $agent)
                <div class="p-3 border-bottom d-flex align-items-start gap-3 {{ $agent->trashed() ? 'opacity-50' : '' }}">
                    {{-- Avatar --}}
                    <div style="width:42px;height:42px;border-radius:12px;background:{{ $agent->is_active ? '#eff6ff' : '#f1f5f9' }};
                         display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;font-size:.9rem;
                         color:{{ $agent->is_active ? '#0369a1' : '#94a3b8' }};">
                        {{ strtoupper(substr($agent->prenom, 0, 1)) }}
                    </div>
                    {{-- Info --}}
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-600" style="font-size:.92rem;">{{ $agent->nom_complet }}</span>
                            @if($agent->trashed())
                                <span class="badge bg-secondary" style="font-size:.7rem;border-radius:8px;">Supprimé</span>
                            @elseif($agent->is_active)
                                <span class="badge" style="background:#dcfce7;color:#166534;font-size:.7rem;border-radius:8px;">Actif</span>
                            @else
                                <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:.7rem;border-radius:8px;">Désactivé</span>
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:.82rem;">{{ $agent->email }}</div>
                        @if($agent->telephone)
                        <div class="text-muted" style="font-size:.8rem;"><i class="bi bi-telephone me-1"></i>{{ $agent->telephone }}</div>
                        @endif
                        <div class="text-muted" style="font-size:.78rem;">Créé le {{ $agent->created_at->format('d/m/Y') }}</div>
                    </div>
                    {{-- Actions --}}
                    @unless($agent->trashed())
                    <div class="d-flex flex-column gap-1 flex-shrink-0">
                        {{-- Activer / désactiver --}}
                        <form method="POST" action="{{ route('admin.agents.toggle', $agent) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $agent->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    style="border-radius:8px;font-size:.78rem;white-space:nowrap;">
                                @if($agent->is_active)
                                    <i class="bi bi-pause-circle me-1"></i>Désactiver
                                @else
                                    <i class="bi bi-play-circle me-1"></i>Activer
                                @endif
                            </button>
                        </form>
                        {{-- Reset mot de passe --}}
                        <button class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.78rem;"
                                data-bs-toggle="modal" data-bs-target="#modalPwd{{ $agent->id }}">
                            <i class="bi bi-key me-1"></i>Nouveau mdp
                        </button>
                        {{-- Supprimer --}}
                        <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}"
                              onsubmit="return confirm('Supprimer cet agent ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:8px;font-size:.78rem;">
                                <i class="bi bi-trash me-1"></i>Supprimer
                            </button>
                        </form>
                    </div>
                    @endunless
                </div>

                {{-- Modal reset password --}}
                @unless($agent->trashed())
                <div class="modal fade" id="modalPwd{{ $agent->id }}" tabindex="-1">
                    <div class="modal-dialog modal-sm modal-dialog-centered">
                        <div class="modal-content" style="border-radius:16px;">
                            <div class="modal-header border-0 pb-0">
                                <h6 class="modal-title fw-600">Réinitialiser le mot de passe</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('admin.agents.reset-password', $agent) }}">
                                @csrf
                                <div class="modal-body">
                                    <p class="text-muted" style="font-size:.85rem;">
                                        Nouveau mot de passe pour <strong>{{ $agent->nom_complet }}</strong>
                                    </p>
                                    <input type="password" name="password" class="form-control" placeholder="Nouveau mot de passe" required minlength="8">
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-sm btn-primary-custom">Réinitialiser</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endunless

                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>
                    Aucun agent créé pour le moment.
                </div>
                @endforelse
            </div>
            @if($agents->hasPages())
            <div class="card-footer bg-transparent border-0 pt-2">
                {{ $agents->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
