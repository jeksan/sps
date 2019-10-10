<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
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
