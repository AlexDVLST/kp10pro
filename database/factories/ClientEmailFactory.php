<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ClientEmail::class, function (Faker $faker) {
    return [
        'email' => $faker->email,
        'default' => 1,
    ];
});
