<?php

namespace App;

use App\BaseModel;

/**
 * Class Client
 * @package App
 */
class Client extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'name', 'country', 'city',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function purse()
    {
        return $this->hasOne(Purse::class, 'client_id');
    }
}
