<?php

namespace App;

use App\BaseModel;

class CurrencyQuote extends BaseModel
{
    protected $fillable = [
        'currency_id', 'date', 'quote',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
