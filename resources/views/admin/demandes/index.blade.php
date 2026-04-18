@extends('layouts.app')
@section('title', 'Gestion des demandes')
@section('page-title', 'Gestion des demandes')

@section('content')

{{-- ── Filtres ─────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-lg-3">
                <input type="text" name="search" class="form-control" placeholder="🔍 Référence, nom, CIN…"
                       value="{{ request('search') }}">
            </div>
            <div class="col-6 col-sm-3 col-lg-2">
                <select name="statut" class="form-select">
                    <option value="">Tous statuts</option>
                    @foreach(\App\Models\Demande::STATUTS_LABELS as $val => $info)
                        <option value="{{ $val }}" {{ request('statut') == $val ? 'selected' : '' }}>
                            {{ $info['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-3 col-lg-2">
                <select name="type" class="form-select">
                    <option value="">Tous types</option>
                    <option value="ordinaire"    {{ request('type') == 'ordinaire'    ? 'selected' : '' }}>Ordinaire</option>
                    <option value="diplomatique" {{ request('type') == 'diplomatique' ? 'selected' : '' }}>Diplomatique</option>
                    <option value="service"      {{ request('type') == 'service'      ? 'selected' : '' }}>Service</option>
                </select>
            </div>
            <div class="col-6 col-sm-3 col-lg-2">
                <select name="urgence" class="form-select">
                    <option value="">Urgence ?</option>
                    <option value="1" {{ request('urgence') == '1' ? 'selected' : '' }}>Urgent</option>
                    <option value="0" {{ request('urgence') == '0' ? 'selected' : '' }}>Normal</option>
                </select>
            </div>
            <div class="col-6 col-sm-3 col-lg-2">
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}"
                       placeholder="Date début">
            </div>
            <div class="col-12 col-sm-6 col-lg-1 d-flex gap-2">
                <button type="submit" class="btn-primary-custom btn flex-grow-1" title="Filtrer">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="{{ route('admin.demandes.index') }}" class="btn btn-outline-secondary flex-grow-1" title="Réinitialiser">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Stats rapides filtres actifs ──────────────────────────── --}}
<div class="d-flex flex-wrap gap-2 mb-3" style="font-size:.82rem;">
    <span class="badge bg-light text-dark border px-3 py-2">
        {{ $demandes->total() }} demande(s) trouvée(s)
    </span>
    @if(request('search') || request('statut') || request('urgence'))
    <span class="badge bg-info text-white px-3 py-2">Filtre actif</span>
    @endif
</div>

{{-- ── Tableau des demandes ───────────────────────────────────── --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.87rem;">
                <thead style="background:#f8f9fc;border-bottom:2px solid #e8ecf0;">
                    <tr>
                        <th class="px-4 py-3">Référence</th>
                        <th>Demandeur</th>
                        <th>Type</th>
                        <th>Motif</th>
                        <th>Montant</th>
                        <th>Soumise le</th>
                        <th>Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                    <tr class="{{ $demande->urgence ? 'table-warning' : '' }}" style="vertical-align:middle;">
                        <td class="px-4">
                            <div class="d-flex align-items-center gap-2">
                                <code style="font-size:.8rem;background:#f1f5f9;padding:3px 8px;border-radius:6px;">
                                    {{ $demande->numero_demande }}
                                </code>
                                @if($demande->urgence)
                                    <i class="bi bi-lightning-charge-fill text-warning" title="Urgent"></i>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-500">{{ $demande->nom_complet }}</div>
                            <div class="text-muted" style="font-size:.78rem;">{{ $demande->user->email }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ ucfirst($demande->type_passeport) }}
                            </span>
                        </td>
                        <td class="text-muted">
                            {{ ucfirst(str_replace('_', ' ', $demande->motif_renouvellement)) }}
                        </td>
                        <td class="fw-600">{{ $demande->montant_formate }}</td>
                        <td class="text-muted">{{ $demande->created_at->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge badge-statut bg-{{ $demande->statut_color }}">
                                {{ $demande->statut_label }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.demandes.show', $demande) }}"
                                   class="btn btn-sm btn-outline-primary" style="border-radius:8px;" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.demandes.pdf', $demande) }}"
                                   class="btn btn-sm btn-outline-secondary" style="border-radius:8px;"
                                   target="_blank" title="PDF">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-muted opacity-50"></i>
                            <div class="text-muted">Aucune demande trouvée</div>
                            @if(request()->hasAny(['search','statut','urgence','type']))
                                <a href="{{ route('admin.demandes.index') }}" class="btn btn-sm btn-link mt-2">
                                    Effacer les filtres
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($demandes->hasPages())
        <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2"
             style="font-size:.85rem;">
            <div class="text-muted">
                Affichage {{ $demandes->firstItem() }}–{{ $demandes->lastItem() }}
                sur {{ $demandes->total() }} résultats
            </div>
            {{ $demandes->links() }}
        </div>
        @endif
    </div>
</div>

@endsection
