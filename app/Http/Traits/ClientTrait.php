<?php

namespace App\Http\Traits;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait ClientTrait
{
    public function addClient($account, Request $request)
    {
        $user = Auth::user();

        //Validate input data
        $request->validate([
            'type'                => 'required|in:1,2,3', //company, human, contact person
            'surname'             => 'nullable|string|max:255',
            'name'                => 'required|string|max:255',
            'middle_name'         => 'nullable|string|max:255',
            'emails.*.email'      => 'nullable|email',
            'phones.*.phone'      => 'nullable|phone',
            'position'            => 'nullable|string',
            'description'         => 'nullable|string',
            'responsibles.*'      => 'nullable|numeric',
            'contactPersons.*.id' => 'nullable|numeric',
            'companyId'           => 'nullable|numeric',
        ]);

        $type             = $request->input('type');
        $surname          = $request->input('surname');
        $name             = $request->input('name');
        $middleName       = $request->input('middle_name');
        $description      = $request->input('description');
        $phones           = $request->input('phones');
        $emails           = $request->input('emails');
        $position         = $request->input('position');
        $responsibles     = $request->input('responsibles');
        $contactPersons   = $request->input('contactPersons');
        $companyId        = $request->input('companyId');

        $client = Client::create([
            'name'        => $name,
            'surname'     => $surname,
            'middle_name' => $middleName,
            'account_id'  => $user->accountId,
            'user_id'     => $user->id
        ]);

        //prepare responsibles
        foreach ($responsibles as &$value) {
            $value = ['user_id' => $value];
        }
        //prepare contact persons
        foreach ($contactPersons as &$value) {
            $value = ['client_contact_person_id' => $value];
        }
        //clean up phones
        foreach ($phones as &$phone) {
            if (isset($phone['phone'])) {
                $phone['phone'] = preg_replace('/[^0-9]/', '', $phone['phone']);
            }
        }

        //If create company. Update relation in contact persons
        if ($type === 1 ) {
            //Get only id
            //TODO: можливо видалити? Те ж саме і для ClientController
            $contactIds = collect($request->input('contactPersons'))->map(function($item){
                return $item;
            });

            Client::whereIn('id', $contactIds)
                ->each(function ($item) use ($client) {
                    $item->companyRelation()->update(['client_company_id' => $client->id]);
                });
        }
        //If create contact person
        if ($type === 3) {
            //If set company
            if ($companyId) {
                //Add client to company
                Client::whereId($companyId)->first()
                    ->contactPersonRelation()->create(['client_contact_person_id' => $client->id]);
            }
        }

        //Save type
        $client->typeValueRelation()
                ->create(['client_type_id' => $type]);
        //Save description
        $client->descriptionRelation()
                ->create(['description' => $description]);
        //Save phones
        $client->phoneRelation()
                ->createMany($phones);
        //Save emails
        $client->emailRelation()
                ->createMany($emails);
        //Save position
        $client->positionRelation()
                ->create(['position' => $position]);
        //Save responsibles
        $client->responsibleRelation()
                ->createMany($responsibles);
        //Save contact person
        $client->contactPersonRelation()
                ->createMany($contactPersons);
        //Save company
        $client->companyRelation()
                ->create(['client_company_id' => $companyId]);

        //Get client with relations
        $client = Client::withoutGlobalScope(ClientScope::class)->with('contactPersonRelation.clientRelation')->whereId($client->id)->first();
        //Fix for js
        $client->displayName = $client->displayName;
        $client->contactPersonRelation->each(function($contactPerson){
            if ($contactPerson->clientRelation) {
                $contactPerson->clientRelation->displayName = $contactPerson->clientRelation->displayName;
            }
        });

        return $client;
    }
}
