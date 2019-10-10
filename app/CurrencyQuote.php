<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrencyQuote extends Model
{
    protected $fillable = [
        'currency_id', 'date', 'quote',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
