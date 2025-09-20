<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'categorie',
        'unite',
    ];

    protected $casts = [
        'categorie' => 'string',
        'unite' => 'string',
    ];

    public function quoteDetails()
    {
        return $this->hasMany(QuoteDetail::class);
    }
}
