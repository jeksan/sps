<?php

namespace App;

use App\BaseModel;

class Client extends BaseModel
{
    protected $fillable = [
        'name', 'last_name', 'patronomic', 'country', 'city',
    ];

    public function purse() {
        return $this->hasOne(Purse::class, 'client_id');
    }
}
