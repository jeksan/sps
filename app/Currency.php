<?php

namespace App;

use App\BaseModel;

class Currency extends BaseModel
{
    protected $fillable = [
        'name', 'quote', 'code',
    ];

    public function currencyQuoteHistory()
    {
        return $this->hasMany(CurrencyQuote::class, 'currency_id', 'id');
    }
}
