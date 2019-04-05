<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ClientDescription::class, function (Faker $faker) {
    return [
        'description' => $faker->paragraph
    ];
});
