<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Product::class, function (Faker $faker) {
    return [
        'account' => 'company',
        'name' => $faker->word,
        'article' => $faker->numberBetween(100, 10000),
        'cost' => $faker->numberBetween(500, 10000),
        'prime_cost' => 0,
        'description' => $faker->text
    ];
});
