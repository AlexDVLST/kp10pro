<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ClientCompany::class, function (Faker $faker) {
    return [
        'client_company_id' => 0
    ];
});
