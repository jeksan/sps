<?php

namespace App;

use App\BaseModel;

class OperationHistory extends BaseModel
{
    const OPERATION_REFILL = 'operation:refill';
    const OPERATION_REMITTANCE = 'operation:remittance';

    protected $fillable = [
        'purse_id', 'date', 'currency_quote', 'amount', 'operation_comment'
    ];

    public function purse()
    {
        return $this->belongsTo(Purse::class, 'purse_id');
    }
}
