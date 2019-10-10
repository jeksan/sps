<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OperationHistory extends Model
{
    protected $fillable = [
        'purse_from', 'purse_to', 'currency_id', 'currency_quote', 'amount', 'date',
    ];

    public function purseFrom()
    {
        return $this->belongsTo(Purse::class, 'purse_from');
    }

    public function purseTo()
    {
        return $this->belongsTo(Purse::class, 'purse_to');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
