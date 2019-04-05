<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ClientPosition::class, function (Faker $faker) {
    return [
        'position' => $faker->word
    ];
});
