<?php

namespace App;

use App\BaseModel;

class Currency extends BaseModel
{
    const SCALE = 6;

    protected $fillable = [
        'name', 'quote', 'code',
    ];

    public function currencyQuoteHistory()
    {
        return $this->hasMany(CurrencyQuote::class, 'currency_id', 'id');
    }
}
