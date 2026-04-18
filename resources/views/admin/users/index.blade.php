@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Utilisateurs</h2>
        <p class="text-muted mb-0">Liste des comptes clients enregistrés</p>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Rechercher..." style="min-width:220px;" />
        <select name="actif" class="form-select" style="width:160px;">
            <option value="">Tous</option>
            <option value="1" {{ request('actif') === '1' ? 'selected' : '' }}>Actifs</option>
            <option value="0" {{ request('actif') === '0' ? 'selected' : '' }}>Désactivés</option>
        </select>
        <button class="btn btn-primary" type="submit">Filtrer</button>
    </form>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>CIN</th>
                        <th>Demandes</th>
                        <th>Statut</th>
                        <th>Créé le</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->nom_complet }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->cin }}</td>
                        <td>{{ $user->demandes_count }}</td>
                        <td>
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                {{ $user->is_active ? 'Actif' : 'Désactivé' }}
                            </span>
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                Voir
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Aucun utilisateur trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $users->links() }}
    </div>
</div>
@endsection
