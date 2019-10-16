<?php

namespace App;

use App\BaseModel;

/**
 * Class Purse
 * @package App
 */
class Purse extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'client_id', 'currency_id', 'balance',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function operationHistory()
    {
        return $this->hasMany(OperationHistory::class, 'purse_id');
    }
}
