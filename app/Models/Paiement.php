<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'demande_id', 'user_id', 'reference_paiement', 'transaction_id',
        'montant', 'devise', 'methode', 'statut',
        'reponse_gateway', 'date_paiement', 'ip_paiement', 'notes',
    ];

    protected $casts = [
        'montant'          => 'decimal:2',
        'reponse_gateway'  => 'array',
        'date_paiement'    => 'datetime',
    ];

    public static function genererReference(): string
    {
        return 'PAY-' . strtoupper(uniqid()) . '-' . date('Ymd');
    }

    public function getMontantFormatteAttribute(): string
    {
        return number_format($this->montant, 0, ',', ' ') . ' ' . $this->devise;
    }

    public function estReussi(): bool
    {
        return $this->statut === 'succes';
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
