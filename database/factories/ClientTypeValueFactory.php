<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ClientTypeValue::class, function (Faker $faker) {
    return [
        'client_type_id' => $faker->randomElement([1, 2, 3])
    ];
});
