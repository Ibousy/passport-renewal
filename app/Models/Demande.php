<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Demande extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_demande', 'user_id', 'traite_par',
        'ancien_numero_passeport', 'date_expiration_ancien',
        'type_passeport', 'motif_renouvellement', 'motif_detail',
        'nom_complet', 'date_naissance', 'lieu_naissance',
        'nationalite', 'cin', 'adresse_residence', 'ville', 'profession',
        'statut', 'commentaire_admin', 'motif_rejet', 'montant_total',
        'date_soumission', 'date_traitement', 'date_validation',
        'date_rdv', 'urgence',
    ];

    protected $casts = [
        'date_naissance'        => 'date',
        'date_expiration_ancien' => 'date',
        'date_soumission'       => 'date',
        'date_traitement'       => 'date',
        'date_validation'       => 'date',
        'date_rdv'              => 'date',
        'urgence'               => 'boolean',
        'montant_total'         => 'decimal:2',
    ];

    // Tarifs selon type & urgence
    const TARIFS = [
        'ordinaire'    => ['normal' => 25000, 'urgent' => 45000],
        'diplomatique' => ['normal' => 50000, 'urgent' => 80000],
        'service'      => ['normal' => 30000, 'urgent' => 55000],
    ];

    const STATUTS_LABELS = [
        'brouillon'              => ['label' => 'Brouillon',               'color' => 'secondary'],
        'soumise'                => ['label' => 'Soumise',                 'color' => 'info'],
        'en_attente_paiement'    => ['label' => 'Attente paiement',        'color' => 'warning'],
        'payee'                  => ['label' => 'Payée',                   'color' => 'primary'],
        'en_cours_traitement'    => ['label' => 'En cours de traitement',  'color' => 'primary'],
        'documents_manquants'    => ['label' => 'Documents manquants',     'color' => 'warning'],
        'validee'                => ['label' => 'Validée',                 'color' => 'success'],
        'rejetee'                => ['label' => 'Rejetée',                 'color' => 'danger'],
        'passeport_pret'         => ['label' => 'Passeport prêt',          'color' => 'success'],
        'delivre'                => ['label' => 'Délivré',                 'color' => 'dark'],
    ];

    // ─── Boot ────────────────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($demande) {
            if (empty($demande->numero_demande)) {
                $demande->numero_demande = static::genererNumero();
            }
            if ($demande->montant_total == 0) {
                $demande->montant_total = static::calculerMontant(
                    $demande->type_passeport,
                    $demande->urgence
                );
            }
        });
    }

    // ─── Accesseurs ──────────────────────────────────────────────────────

    public function getStatutLabelAttribute(): string
    {
        return static::STATUTS_LABELS[$this->statut]['label'] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return static::STATUTS_LABELS[$this->statut]['color'] ?? 'secondary';
    }

    public function getMontantFormatteAttribute(): string
    {
        return number_format($this->montant_total, 0, ',', ' ') . ' XOF';
    }

    // ─── Helpers statiques ───────────────────────────────────────────────

    public static function genererNumero(): string
    {
        $annee = date('Y');
        $derniere = static::whereYear('created_at', $annee)->count() + 1;
        return sprintf('REF-%s-%05d', $annee, $derniere);
    }

    public static function calculerMontant(string $type, bool $urgent): float
    {
        $tarifs = static::TARIFS[$type] ?? static::TARIFS['ordinaire'];
        return $urgent ? $tarifs['urgent'] : $tarifs['normal'];
    }

    public function estModifiable(): bool
    {
        return in_array($this->statut, ['brouillon', 'documents_manquants']);
    }

    public function estPayee(): bool
    {
        return in_array($this->statut, [
            'payee', 'en_cours_traitement', 'validee', 'passeport_pret', 'delivre'
        ]);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────

    public function scopeParStatut($query, string $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeRecentes($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeUrgentes($query)
    {
        return $query->where('urgence', true);
    }

    // ─── Relations ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function paiementSucces()
    {
        return $this->hasOne(Paiement::class)->where('statut', 'succes')->latest();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
