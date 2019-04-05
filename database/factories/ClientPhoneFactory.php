<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ClientPhone::class, function (Faker $faker) {
    return [
        'phone' => $faker->numberBetween(5000000000, 8000000000),
        'default' => 1,
    ];
});
