<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel
 * @package App
 */
class BaseModel extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;
}
