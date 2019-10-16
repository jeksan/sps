<?php

namespace App;

use App\BaseModel;

/**
 * Class CurrencyQuote
 * @package App
 */
class CurrencyQuote extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'currency_id', 'date', 'quote',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
