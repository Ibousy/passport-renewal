<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande {{ $demande->numero_demande }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 24px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .title { font-size: 1.3rem; font-weight: 700; }
        .subtitle { color: #555; margin-top: 6px; }
        .section { margin-bottom: 20px; }
        .section h2 { font-size: 1rem; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 12px; color: #333; }
        .field { margin-bottom: 8px; }
        .label { font-weight: 700; color: #222; display: inline-block; width: 160px; }
        .value { color: #333; }
        .box { padding: 16px; border: 1px solid #ddd; border-radius: 10px; background: #fafafa; }
        .documents li { margin-bottom: 6px; }
        .footer { margin-top: 32px; color: #666; font-size: .85rem; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">Dossier de demande</div>
            <div class="subtitle">Référence : {{ $demande->numero_demande }}</div>
        </div>
        <div style="text-align:right;">
            <div>Date : {{ $demande->created_at->format('d/m/Y') }}</div>
            <div>Statut : {{ ucfirst($demande->statut) }}</div>
        </div>
    </div>

    <div class="section">
        <h2>Informations du demandeur</h2>
        <div class="box">
            <div class="field"><span class="label">Nom complet :</span> <span class="value">{{ $demande->nom_complet }}</span></div>
            <div class="field"><span class="label">Adresse email :</span> <span class="value">{{ $demande->user->email }}</span></div>
            <div class="field"><span class="label">Téléphone :</span> <span class="value">{{ $demande->user->telephone }}</span></div>
            <div class="field"><span class="label">CIN :</span> <span class="value">{{ $demande->cin }}</span></div>
            <div class="field"><span class="label">Date de naissance :</span> <span class="value">{{ $demande->date_naissance->format('d/m/Y') }}</span></div>
            <div class="field"><span class="label">Lieu de naissance :</span> <span class="value">{{ $demande->lieu_naissance }}</span></div>
            <div class="field"><span class="label">Nationalité :</span> <span class="value">{{ $demande->nationalite }}</span></div>
        </div>
    </div>

    <div class="section">
        <h2>Détails de la demande</h2>
        <div class="box">
            <div class="field"><span class="label">Type de passeport :</span> <span class="value">{{ ucfirst($demande->type_passeport) }}</span></div>
            <div class="field"><span class="label">Motif de renouvellement :</span> <span class="value">{{ ucfirst(str_replace('_', ' ', $demande->motif_renouvellement)) }}</span></div>
            <div class="field"><span class="label">Adresse :</span> <span class="value">{{ $demande->adresse_residence }}</span></div>
            <div class="field"><span class="label">Ville :</span> <span class="value">{{ $demande->ville }}</span></div>
            <div class="field"><span class="label">Montant total :</span> <span class="value">{{ number_format($demande->montant_total, 0, ',', ' ') }} FCFA</span></div>
            <div class="field"><span class="label">Urgence :</span> <span class="value">{{ $demande->urgence ? 'Oui' : 'Non' }}</span></div>
            <div class="field"><span class="label">Soumise le :</span> <span class="value">{{ $demande->date_soumission?->format('d/m/Y') ?? $demande->created_at->format('d/m/Y') }}</span></div>
            @if($demande->date_rdv)
                <div class="field"><span class="label">Date RDV :</span> <span class="value">{{ $demande->date_rdv->format('d/m/Y') }}</span></div>
            @endif
            @if($demande->commentaire_admin)
                <div class="field"><span class="label">Commentaire admin :</span> <span class="value">{{ $demande->commentaire_admin }}</span></div>
            @endif
            @if($demande->motif_rejet)
                <div class="field"><span class="label">Motif rejet :</span> <span class="value">{{ $demande->motif_rejet }}</span></div>
            @endif
        </div>
    </div>

    <div class="section">
        <h2>Documents joints</h2>
        <div class="box documents">
            @if($demande->documents->isEmpty())
                <div>Aucun document joint.</div>
            @else
                <ul>
                    @foreach($demande->documents as $document)
                        <li>{{ \App\Models\Document::TYPES_LABELS[$document->type_document] ?? $document->nom_original }} - {{ $document->nom_original }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y H:i') }}.
    </div>
</body>
</html>
