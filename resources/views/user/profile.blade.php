@extends('layouts.app')
@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')
@php $user = auth()->user(); @endphp

<div class="row justify-content-center">
<div class="col-12 col-lg-8">

@if($errors->any())
<div class="alert alert-danger" style="border-radius:12px;">
    <strong><i class="bi bi-exclamation-circle me-2"></i>Erreurs :</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

{{-- Avatar + nom --}}
<div class="card mb-4">
    <div class="card-body p-4 d-flex align-items-center gap-4">
        <div style="width:72px;height:72px;border-radius:18px;background:var(--primary);
             color:#fff;display:flex;align-items:center;justify-content:center;
             font-size:1.8rem;font-weight:700;flex-shrink:0;">
            {{ strtoupper(substr($user->prenom ?? 'U', 0, 1)) }}
        </div>
        <div>
            <h4 class="mb-1" style="font-family:'Playfair Display',serif;color:var(--primary);">
                {{ $user->nom_complet }}
            </h4>
            <div class="text-muted" style="font-size:.87rem;">
                <i class="bi bi-envelope me-1"></i>{{ $user->email }}
            </div>
            <span class="badge mt-1" style="background:#e0f2fe;color:#0369a1;border-radius:20px;">
                {{ $user->isAdmin() ? 'Administrateur' : 'Utilisateur' }}
            </span>
        </div>
    </div>
</div>

{{-- Formulaire --}}
<div class="card">
    <div class="card-header"><i class="bi bi-person-gear me-2"></i>Modifier mes informations</div>
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')
        <div class="card-body p-4">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                    <input type="text" name="prenom" class="form-control" required
                           value="{{ old('prenom', $user->prenom) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control" required
                           value="{{ old('nom', $user->nom) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control"
                           placeholder="Ex: +221 77 000 00 00"
                           value="{{ old('telephone', $user->telephone) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Numéro CIN</label>
                    <input type="text" name="cin" class="form-control"
                           value="{{ old('cin', $user->cin) }}">
                </div>

                <div class="col-md-8">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="adresse" class="form-control"
                           value="{{ old('adresse', $user->adresse) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" class="form-control"
                           value="{{ old('ville', $user->ville) }}">
                </div>

                <div class="col-12">
                    <hr class="my-1">
                    <div class="text-muted" style="font-size:.8rem;">
                        <i class="bi bi-lock me-1"></i>
                        Pour changer votre mot de passe, contactez l'administrateur.
                    </div>
                </div>

            </div>
        </div>
        <div class="card-footer bg-transparent d-flex gap-3">
            <button type="submit" class="btn-primary-custom btn">
                <i class="bi bi-floppy me-2"></i>Enregistrer les modifications
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" style="border-radius:10px;">
                Annuler
            </a>
        </div>
    </form>
</div>

</div>
</div>
@endsection
