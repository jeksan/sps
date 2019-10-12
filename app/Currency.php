<?php

namespace App;

use App\BaseModel;

class Currency extends BaseModel
{
    protected $fillable = [
        'name', 'quote', 'code',
    ];

    public function client()
    {
        return $this->hasOne(Client::class, 'client_id');
    }

    public function currencyQuotes()
    {
        return $this->hasMany(CurrencyQuote::class, 'currency_id', 'id');
    }
}
