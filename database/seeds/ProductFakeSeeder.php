<?php

use Illuminate\Database\Seeder;

class ProductFakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Product::class, 5000)->create();
    }
}
