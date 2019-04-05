<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\ClientType;
use App\Models\ClientTypeValue;
use App\Models\ClientDescription;
use App\Models\ClientPhone;
use App\Models\ClientEmail;
use App\Models\ClientCompany;
use App\Models\ClientContactPerson;
use App\Http\Traits\ClientTrait;

class ClientController extends Controller
{
    use ClientTrait;

    public function __construct()
    {
        //Permissions
        $this->middleware(['permission:view client|view-own client']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($account)
    {
        $user = Auth::user();
        $page = Page::whereSlug('client')->first();

        return view('pages.clients', ['user' => $user, 'page' => $page]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();

        //Premission
        // if (!$user->can('create client')) {
        if (!$user->userCan('create client')) {
            abort(403);
        }

        $page = Page::whereSlug('client')->first();

        return view('pages.client-create', ['user' => $user, 'page' => $page]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($account, Request $request)
    {
        $user = Auth::user();

        //Premission
        // if (!$user->can('create client')) {
        if (!$user->userCan('create client')) {
            return response()->json(['errors' => __('messages.permission.denied')], 422);
        }

        //Use trait for create client
        $client = $this->addClient($account, $request);

        return response()->json($client);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Client  $Client
     * @return \Illuminate\Http\Response
     */
    public function show(Client $Client)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Client  $Client
     * @return \Illuminate\Http\Response
     */
    public function edit($account, $id)
    {
        $user   = Auth::user();
        $client = Client::whereId($id)->first();

        //Permission
        // if (!$user->can('view client') && ($user->can('view-own client') && !$client)) {
        if ($user->userCan('view-own client') && !$client) {
            abort(403);
        }

        $page = Page::whereSlug('client')->first();

        return view('pages.client-edit', ['user' => $user, 'page' => $page, 'clientId' => $id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param string $account
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update($account, Request $request, $id)
    {
        $user   = Auth::user();
        $client = Client::whereId($id)->first();

        //Premission
        if (!$this->checkPermission('edit', $client)) {
            return response()->json(['errors' => __('messages.permission.denied')], 422);
        }

        // //Validate input data
        $this->validateInput($request);

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

        //Error if client not found
        if (!$client) {
            return response()->json(['errors' => __('messages.client.not_found')], 422);
        }
        //Fix for responsibles
        if (!$responsibles) {
            $responsibles = [0];
        }
        //Fix for contact person
        if (!$contactPersons) {
            $contactPersons = [0];
        }

        //prepare responsibles
        foreach ($responsibles as &$value) {
            $value = ['user_id' => $value];
        }
        //prepare contact persons
        foreach ($contactPersons as &$value) {
            $value = ['client_contact_person_id' => $value];
        }

        $client->name        = $name;
        $client->surname     = $surname;
        $client->middle_name = $middleName;

        //Clean up phones
        foreach ($phones as &$phone) {
            $phone['phone'] = preg_replace('/[^0-9]/', '', $phone['phone']);
        }

        //Update relation in contact persons. Only for company
        if ($type === 1) {
            //Clear current saved relation
            $client->contactPersonRelation->each(function ($item) {
                if ($item->clientRelation) {
                    $item->clientRelation->companyRelation()
                        ->update(['client_company_id' => 0]);
                }
            });

            //Get only id
            $contactIds = collect($request->input('contactPersons'))->map(function ($item) {
                return $item;
            });

            //If converted from something else
            $companyId = 0;
            
            Client::whereIn('id', $contactIds)
                ->each(function ($item) use ($client) {
                    $item->companyRelation()->update(['client_company_id' => $client->id]);
                });
        }

        //Update contact person
        if ($type === 3) {
            //If stored company not equals new one
            if ($client->companyId != $companyId) {
                //Previous saved company
                if ($client->companyId) {
                    //Find client
                    $clientContactPerson = Client::whereId($client->companyId)->first();
                    if($clientContactPerson){
                        $clientContactPerson = $clientContactPerson->contactPersonRelation();
                        //Check each contact person
                        $clientContactPerson->each(function ($item) use ($client) {
                            if ($item->client_contact_person_id == $client->id) {
                                //Delete contact person from the company
                                $item->delete();
                            }
                        });
                    }
                }
                //If set company
                if ($companyId) {
                    //Add client to company
                    Client::whereId($companyId)->first()
                        ->contactPersonRelation()->create(['client_contact_person_id' => $client->id]);
                }
            }
        }

        //Delete all phones
        $client->phoneRelation()->delete();
        //Delete all emails
        $client->emailRelation()->delete();
        //Delete all responsible
        $client->responsibleRelation()->delete();
        //Delete all contact presons
        $client->contactPersonRelation()->delete();

        //Save type
        $client->typeValueRelation()
                ->update(['client_type_id' => $type]);
        //Save description
        $client->descriptionRelation()
                ->update(['description' => $description]);
        //Save phones
        $client->phoneRelation()
                ->createMany($phones);
        //Save emails
        $client->emailRelation()
                ->createMany($emails);
        //Save position
        $client->positionRelation()
                ->update(['position' => $position]);
        //Save responsibles
        $client->responsibleRelation()
                ->createMany($responsibles);
        //Save contact person
        $client->contactPersonRelation()
                ->createMany($contactPersons);
        //Save company
        $client->companyRelation()
                ->update(['client_company_id' => $companyId]);

        try {
            //Store
            $client->save();
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.client.update.success')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Client  $Client
     * @return \Illuminate\Http\Response
     */
    public function destroy($account, $id)
    {
        $client = Client::whereId($id)->first();

        if (!$this->checkPermission('delete', $client)) {
            return response()->json(['errors' => __('messages.permission.denied')], 422);
        }

        //Error if client not found
        if (!$client) {
            return response()->json(['errors' => __('messages.client.not_found')], 422);
        }

        try {
            $client->delete();
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.client.delete.success')]);
    }

    /**
     * Get client list json
     *
     * @param string $account
     * @return json
     */
    public function listJson($account, Request $request)
    {
        $search   = $request->input('search');
        $paginate = $request->input('paginate') ? $request->input('paginate') : 10;

        $query = Client::with('typeValueRelation.typeRelation', 'companyRelation.clientRelation', 'responsibleRelation');

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('surname', 'LIKE', "%{$search}%")
                    ->orWhere('middle_name', 'LIKE', "%{$search}%");
            });
        }

        $clients = $query->paginate($paginate);

        if ($clients->isNotEmpty()) {
            $clients->each(function ($client) {
                $client->displayName = $client->displayName;
                $client->typeId = $client->typeId;
                $client->typeName = $client->typeName;
                $client->companyId = $client->companyId;
                $client->companyName = $client->companyName;
                //Get permission for each
                $client->permissions = $this->getPermissons($client);
            });
        }

        return response()->json($clients, 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Get client card in json
     *
     * @param string $account
     * @param int $id
     * @return json
     */
    public function json($account, $id)
    {
        $client = Client::whereId($id)
                ->with('typeValueRelation', 'descriptionRelation', 'contactPersonRelation.clientRelation', 'positionRelation', 'companyRelation.clientRelation', 'phoneRelation', 'emailRelation', 'responsibleRelation')
                ->first();

        if ($client) {
            $client->permissions = $this->getPermissons($client);
            $client->contactPersonRelation->each(function (&$item) {
                if ($item->clientRelation) {
                    $item->clientRelation->displayName = $item->clientRelation->displayName;
                }
            });

            // $data['id']           = $client->id;
            // $data['user_id']      = $client->user_id;
            // $data['name']         = $client->name;
            // $data['email']        = $client->email;
            // $data['surname']      = $client->surname;
            // $data['middle_name']  = $client->middle_name;
            // $data['type']         = $client->typeId;
            // $data['description']  = $client->description;
            // $data['phones']       = $client->phones;
            // $data['emails']       = $client->emails;
            // $data['responsibles'] = $client->responsibleRelation->pluck('user_id');
            // $data['position']     = $client->position;
            // $data['companyId']    = $client->companyId;
            // $data['companyName']  = $client->companyName;
            // $data['permissions']  = $this->getPermissons($client);

            // $client->contactPersonRelation->each(function ($item) use (&$data) {
            //     if ($item->clientRelation) {
            //         $data['contactPersons'][] = [
            //             'id'          => $item->clientRelation->id,
            //             'displayName' => $item->clientRelation->displayName,
            //         ];
            //     }
            // });

            return response()->json($client, 200, [], JSON_NUMERIC_CHECK);
        }

        return response()->json(['errors' => __('messages.client.not_found')], 422);
    }

    public function validateInput(Request $request)
    {
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
    }

    /**
     * Check permission with own
     *
     * @param string $name
     * @param Client $client
     * @return boolean
     */
    public function checkPermission($name, $client)
    {
        $user = Auth::user();

        // if ($user->can($name . ' client')) {
        if ($user->userCan($name . ' client')) {
            return true;
        }
        // if ($user->can($name . '-own client') && $client->user_id == $user->id) {
        // if ($user->userCan($name . '-own client') && $client->user_id == $user->id) {
        if ($user->userCan($name . '-own client') && $client->user_id == $user->id) {
            return true;
        }
        if ($client->responsibleRelation) {
            //Check responsible
            $responsible = $client->responsibleRelation->filter(function ($item) use ($user) {
                return $item->user_id == $user->id;
            })->first();

            if ($responsible) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get permissions for current user
     *
     * @param Client $client
     * @return void
     */
    public function getPermissons(Client $client)
    {
        return [
            'create' => $this->checkPermission('create', $client),
            'view'   => $this->checkPermission('view', $client),
            'edit'   => $this->checkPermission('edit', $client),
            'delete' => $this->checkPermission('delete', $client)
        ];
    }
    
    /**
     * Add email to client
     *
     * @param string $account
     * @param int $id
     * @param Request $request
     * @return json
     */
    public function addEmail($account, $id, Request $request)
    {
        //Validate input data
        $request->validate([
            'email' => 'required|email',
        ]);

        $client = Client::whereId($id)->first();

        //Premission
        if (!$this->checkPermission('edit', $client)) {
            return response()->json(['errors' => __('messages.permission.denied')], 422);
        }
        //Error if client not found
        if (!$client) {
            return response()->json(['errors' => __('messages.client.not_found')], 422);
        }

        try {
            $client->emailRelation()->create([
                'email' => $request->get('email')
            ]);
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.client.update.success')]);
    }
}
