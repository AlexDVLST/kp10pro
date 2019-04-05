<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Client::class, function (Faker $faker) {
    return [
        'account' => 'company',
        'surname' => $faker->firstName('male'),
        'name' => $faker->lastName('male'),
        'middle_name' => '',
        'user_id' => 0
    ];
});
