<?php
use Faker\Generator as Faker;

$factory->define(App\Client::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name,
        'country' => $faker->country,
        'city' => $faker->city,
    ];
});