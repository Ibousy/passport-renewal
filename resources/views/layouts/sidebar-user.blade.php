{{-- resources/views/layouts/sidebar-user.blade.php --}}

<div class="nav-section-title">Principal</div>
<a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <i class="bi bi-grid-1x2-fill"></i> Tableau de bord
</a>
<a href="{{ route('demandes.index') }}" class="nav-link {{ request()->routeIs('demandes.*') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-person"></i> Mes demandes
    @php $enCours = auth()->user()->demandes()->whereNotIn('statut', ['validee','rejetee','delivre','brouillon'])->count(); @endphp
    @if($enCours > 0)
        <span class="badge-notif">{{ $enCours }}</span>
    @endif
</a>
<a href="{{ route('demandes.create') }}" class="nav-link {{ request()->routeIs('demandes.create') ? 'active' : '' }}">
    <i class="bi bi-plus-circle"></i> Nouvelle demande
</a>

<div class="nav-section-title mt-2">Compte</div>
<a href="{{ route('notifications') }}" class="nav-link {{ request()->routeIs('notifications') ? 'active' : '' }}">
    <i class="bi bi-bell"></i> Notifications
    @php $nonLues = auth()->user()->notificationsNonLues()->count(); @endphp
    @if($nonLues > 0)
        <span class="badge-notif">{{ $nonLues }}</span>
    @endif
</a>
<a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="bi bi-person-circle"></i> Mon profil
</a>
