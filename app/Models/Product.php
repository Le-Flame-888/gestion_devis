<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'prix_unitaire',
        'unite',
        'stock',
    ];

    public function quoteDetails()
    {
        return $this->hasMany(QuoteDetail::class);
    }
}
