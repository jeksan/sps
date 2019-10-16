<?php

namespace App;

use App\BaseModel;

/**
 * Class OperationHistory
 * @package App
 */
class OperationHistory extends BaseModel
{
    const OPERATION_REFILL = 'operation:refill';
    const OPERATION_REMITTANCE = 'operation:remittance';

    /**
     * @var array
     */
    protected $fillable = [
        'purse_id', 'date', 'currency_quote', 'amount', 'operation_comment'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purse()
    {
        return $this->belongsTo(Purse::class, 'purse_id');
    }
}
