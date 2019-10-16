<?php

namespace App;

use App\BaseModel;

/**
 * Class Currency
 * @package App
 */
class Currency extends BaseModel
{
    const SCALE = 6;

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'quote', 'code',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function currencyQuoteHistory()
    {
        return $this->hasMany(CurrencyQuote::class, 'currency_id', 'id');
    }
}
