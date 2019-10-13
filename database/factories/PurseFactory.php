<?php
use Faker\Generator as Faker;

$factory->define(App\Purse::class, function (Faker $faker) {
    return [
        'currency_id' => \App\Currency::inRandomOrder()->first()->id,
        'balance' => 0,
    ];
});
