<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purse extends Model
{
    protected $fillable = [
        'client_id', 'currency_id', 'balance',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
