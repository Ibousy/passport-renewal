<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id', 'demande_id', 'type', 'titre',
        'message', 'lu', 'canal', 'lu_le',
    ];

    protected $casts = [
        'lu'    => 'boolean',
        'lu_le' => 'datetime',
    ];

    public function marquerLu(): void
    {
        $this->update(['lu' => true, 'lu_le' => now()]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }
}
