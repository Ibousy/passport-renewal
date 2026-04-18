<?php
// ════════════════════════════════════════════════════════════════════
// app/Models/Document.php
// ════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'demande_id', 'user_id', 'type_document',
        'nom_original', 'nom_fichier', 'chemin_fichier',
        'mime_type', 'taille_octets', 'statut', 'commentaire', 'hash_fichier',
    ];

    const TYPES_REQUIS = [
        'ancien_passeport'  => 'Ancien passeport',
        'carte_identite'    => 'Carte d\'identité',
        'photo_identite'    => 'Photo d\'identité',
        'acte_naissance'    => 'Acte de naissance',
    ];

    const TYPES_LABELS = [
        'ancien_passeport'       => 'Ancien passeport',
        'carte_identite'         => 'Carte d\'identité nationale',
        'photo_identite'         => 'Photo d\'identité récente',
        'acte_naissance'         => 'Acte de naissance',
        'justificatif_domicile'  => 'Justificatif de domicile',
        'declaration_perte'      => 'Déclaration de perte/vol',
        'autre'                  => 'Autre document',
    ];

    public function getUrlAttribute(): string
    {
        return Storage::url($this->chemin_fichier);
    }

    public function getTailleFormateeAttribute(): string
    {
        $taille = $this->taille_octets;
        if ($taille < 1024) return $taille . ' B';
        if ($taille < 1048576) return round($taille / 1024, 1) . ' KB';
        return round($taille / 1048576, 1) . ' MB';
    }

    public function estImage(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/webp']);
    }

    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
