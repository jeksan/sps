<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'last_name', 'patronomic', 'country', 'city',
    ];

    public function purse() {
        return $this->hasOne(Purse::class, 'client_id');
    }
}
