<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ClientFakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Client::class, 10000)->create()->each(function($u){
            $u->companyRelation()->save(factory(App\Models\ClientCompany::class)->make());
            $u->contactPersonRelation()->save(factory(App\Models\ClientContactPerson::class)->make());
            $u->descriptionRelation()->save(factory(App\Models\ClientDescription::class)->make());
            $u->emailRelation()->save(factory(App\Models\ClientEmail::class)->make());
            $u->phoneRelation()->save(factory(App\Models\ClientPhone::class)->make());
            $u->positionRelation()->save(factory(App\Models\ClientPosition::class)->make());
            $u->responsibleRelation()->save(factory(App\Models\ClientResponsible::class)->make(['user_id' => 1]));
            $u->typeValueRelation()->save(factory(App\Models\ClientTypeValue::class)->make());

            if($u->typeId == 1){
                $u->name = 'ООО "'.$u->name.'"';
                $u->save();
            }
        });
    }
}
