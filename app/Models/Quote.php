<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Client;
use App\Models\QuoteDetail;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_devis',
        'client_id',
        'user_id',
        'date_devis',
        'date_validite',
        'statut',
        'total_ht',
        'tva',
        'total_ttc',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function details()
    {
        return $this->hasMany(QuoteDetail::class, 'quote_id');
    }
}
