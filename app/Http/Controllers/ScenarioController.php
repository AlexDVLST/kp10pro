<?php

namespace App\Http\Controllers;

use App\Events\OfferStatusChanged;
use App\Helpers\Amocrm;
use App\Helpers\Bitrix24;
use App\Helpers\MegaplanV3;
use App\Http\Traits\AmocrmTrait;
use App\Http\Traits\Bitrix24Trait;
use App\Http\Traits\MegaplanTrait;
use App\Http\Traits\OfferTrait;
use App\Jobs\ClientNotOpenLetterJob;
use App\Jobs\ClientOpenLetterJob;
use App\Jobs\ManagerSavedKpJob;
use App\Jobs\ManagerSentLetter;
use App\Jobs\SelectedVariantJob;
use App\Models\Integration;
use App\Models\IntegrationMegaplan;
use App\Models\Offer;
use App\Models\OfferAmoCrmDeal;
use App\Models\OfferBitrix24Deal;
use App\Models\OfferHistory;
use App\Models\OfferMegaplanDeal;
use App\Models\Scenario;
use App\Models\ScenarioAction;
use App\Models\ScenarioEvent;
use App\Models\SystemOfferState;
use App\Models\SystemScenarioAdditionalEvent;
use App\Models\SystemScenarioEvent;
use App\Models\SystemScenarioAction;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScenarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $page = Page::whereSlug('settings-scenario')->first();

        return view('pages.settings.scenario', [
            'user' => $user,
            'page' => $page
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Get scenario list in json
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listJson()
    {
        //get scenario list
        $scenario = Scenario::with('scenarioEvent', 'scenarioAction', 'events', 'actions')->get();

        if ($scenario->isNotEmpty()) {
            return response()->json(['status' => true, 'scenario' => $scenario]);
        }

        return response()->json(['status' => false]);
    }

    /**
     * Получить список системных условий
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEventsList()
    {
        $integration = Integration::first();

        if ($integration) {
            $events = SystemScenarioEvent::whereNotIn('id', [3, 4])->get();
        } else {
            $events = SystemScenarioEvent::whereNotIn('id', [3, 4, 9])->get();
        }

        if ($events->isNotEmpty()) {
            return response()->json($events);
        }

        return response()->json(['errors' => __('messages.scenario.events.list.empty')], 422);
    }

    /**
     * Поиск сценариев с одинаковыми условиями
     */
    /*public function getSameScenario($eventId, $actions)
    {
        $scenario = Scenario::whereEventId($eventId)->get();

        $removedActions = [];
        if ($scenario->isNotEmpty()) {

            switch($eventId){
                case 2:
                    break;
                case 8:
                    break;
                case 9:
                    break;
                default:

                    $priceCondition = false;
                    foreach ($scenario as $item) {
                        $actionId = $item['action_id'];

                        $removedActions[] = $actionId;

                        if($actionId == 1 || $actionId == 2 || $actionId == 3 || $actionId == 6){
                            $priceCondition = true;
                        }
                    }
                    break;
            }
        }

        Log::debug(print_r($removedActions, 1));

        return $actions;
    }*/

    /**
     * Получить список системных действий
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActionsList($account, $id)
    {
        $additionalEventsList = [];
        $actionsList = [];
        switch ($id) {
            case 1:
            case 3:
            case 4:
            case 5:
//                $actionsList = $this->getSameScenario($id, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);
                $actionsList = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                break;
            case 2:
                $actionsList = [4, 7, 8, 9, 10, 11];
                break;
            case 6:
                $actionsList = [1, 2, 3, 5, 6, 9, 10, 11];
                break;
            case 7:
                $actionsList = [4, 7, 8, 9, 10];
                break;
            case 8:
                $actionsList = [8];
                break;
            case 9:
                $additionalEventsList = SystemOfferState::all();
                $actionsList = [1, 2, 3, 4, 5, 6, 7, 9, 10, 11];
                break;
        }

        Log::debug(print_r($actionsList, 1));

        $integration = Integration::first();

        if ($integration) {
            switch ($integration->system_crm_id) {
                case 1://Megaplan

                    //получить дополнительные условия в виде статусов crm
                    if ($id == 8) {
                        $Megaplan = new MegaplanV3();
                        $programs = $Megaplan->getDealProgram(['fields' => ['states']]);

                        if ($programs) {
                            $additionalEventsList['crm_type'] = 1;
                            $additionalEventsList['programs'] = $this->prepareStatesListByCrm($programs, 1);
                        }
                    }
                    break;
                case 2: //AmoCRM
                    $key = array_search(5, $actionsList);
                    if (false !== $key) {
                        unset($actionsList[$key]);
                    }

                    if ($id == 8) {
                        $Amocrm = new Amocrm();
                        $pipelines = $Amocrm->getPipelines();

                        if ($pipelines) {
                            $additionalEventsList['crm_type'] = 2;
                            $additionalEventsList['programs'] = $this->prepareStatesListByCrm($pipelines, 2);
                        }
                    }
                    break;
                case 3://Bitrix24

                    if ($id == 8) {

                        $B24 = new Bitrix24();
                        $pipelines = $B24->getPipelines(['filter' => ['IS_LOCKED' => 'N']]);
                        $pipelines[] = [
                            'ID'   => 0,
                            'NAME' => 'Общая'
                        ];

                        $additionalEventsList['crm_type'] = 3;
                        $eventsList = [];
                        foreach ($pipelines as $pipeline) {

                            $stages = $B24->getListDealStatus(['id' => $pipeline['ID']]);

                            $states = [];
                            if ($stages) {
                                foreach ($stages as $stage) {
                                    $states[] = [
                                        'id'   => $stage['STATUS_ID'],
                                        'name' => $stage['NAME']
                                    ];
                                }
                            }

                            $eventsList[] = [
                                'program_id'   => $pipeline['ID'],
                                'program_name' => $pipeline['NAME'],
                                'states'       => $states
                            ];
                        }

                        $additionalEventsList['programs'] = $eventsList;
                    }

                    break;
            }
        } else {
            for ($i = 1; $i <= 7; $i++) {

                //поиск значения в массиве с возвращением ключа массива
                $key = array_search($i, $actionsList);
                if (false !== $key) {
                    unset($actionsList[$key]);
                }
            }
        }

        if ($actionsList) {
            $actions = SystemScenarioAction::whereIn('id', $actionsList)->get();

            return response()->json(['actions' => $actions, 'additional_events' => $additionalEventsList]);
        }

        return response()->json(['errors' => __('messages.scenario.actions.list.empty')], 422);
    }

    /**
     *
     * @param $account
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdditionalActionsList($account, $id)
    {
        $additionalActionsList = [];
        switch ($id) {
            case 7: //получить дополнительные условия в виде статусов crm

                $integration = Integration::first();
                if ($integration) {

                    switch ($integration->system_crm_id) {
                        case 1://Megaplan
                            $Megaplan = new MegaplanV3();
                            $programs = $Megaplan->getDealProgram(['fields' => ['states']]);

                            if ($programs) {
                                $additionalActionsList['crm_type'] = 1;
                                $additionalActionsList['programs'] = $this->prepareStatesListByCrm($programs, 1);
                            }

                            break;
                        case 2: //AmoCRM

                            $Amocrm = new Amocrm();
                            $pipelines = $Amocrm->getPipelines();

                            if ($pipelines) {
                                $additionalActionsList['crm_type'] = 2;
                                $additionalActionsList['programs'] = $this->prepareStatesListByCrm($pipelines, 2);
                            }

                            break;
                        case 3://Bitrix24

                            $B24 = new Bitrix24();
                            $pipelines = $B24->getPipelines(['filter' => ['IS_LOCKED' => 'N']]);
                            $pipelines[] = [
                                'ID'   => 0,
                                'NAME' => 'Общая'
                            ];

                            $additionalActionsList['crm_type'] = 3;
                            $eventsList = [];
                            foreach ($pipelines as $pipeline) {

                                $stages = $B24->getListDealStatus(['id' => $pipeline['ID']]);

                                $states = [];
                                if ($stages) {
                                    foreach ($stages as $stage) {
                                        $states[] = [
                                            'id'   => $stage['STATUS_ID'],
                                            'name' => $stage['NAME']
                                        ];
                                    }
                                }

                                $eventsList[] = [
                                    'program_id'   => $pipeline['ID'],
                                    'program_name' => $pipeline['NAME'],
                                    'states'       => $states
                                ];
                            }

                            $additionalActionsList['programs'] = $eventsList;
                            break;
                    }
                }

                break;
            case 8: //Получить список статусов КП
                $additionalActionsList = SystemOfferState::all();
                break;
        }

        return response()->json($additionalActionsList);
    }

    /**
     * Добавить новый сценарий
     *
     * @param $account
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addNewScenario($account, Request $request)
    {
        $event = $request->input('event');
        $additionalEvent = $request->input('additional_event');
        $action = $request->input('action');
        $additionalAction = $request->input('additional_action');

        $eventId = $event[0]['id'];
        $actionId = $action[0]['id'];

        $exist = Scenario::whereEventId($eventId)->whereActionId($actionId)->first();

        if (!$exist) {

            try {
                $user = Auth::user();

                $scenario = Scenario::create([
                    'account_id' => $user->accountId,
                    'event_id'   => $eventId,
                    'action_id'  => $actionId
                ]);

                if ($scenario) {

                    $scenarioId = $scenario->id;

                    $events = !empty($additionalEvent[0]) ? $additionalEvent[0] : [];
                    if ($events) {
                        foreach ($events as $i => $event) {
                            $events[$i]['scenario_id'] = $scenarioId;
                        }

                        ScenarioEvent::insert($events);
                    }

                    $actions = !empty($additionalAction[0]) ? $additionalAction[0] : [];
                    if ($actions) {
                        foreach ($actions as $i => $action) {
                            $actions[$i]['scenario_id'] = $scenarioId;
                        }

                        ScenarioAction::insert($actions);
                    }
                }

                return response()->json(['status' => true]);
            } catch (Exception $e) {
                return response()->json(['errors' => $e->getMessage()], 422);
            }
        }

        return response()->json(['errors' => __('messages.scenario.exist')], 422);
    }

    /**
     * Удалить указанный сценарий
     *
     * @param $account
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteScenario($account, Request $request)
    {
        $eventId = $request->input('event_id');
        $actionId = $request->input('action_id');

        $scenario = Scenario::whereEventId($eventId)->whereActionId($actionId)->first();

        if (!$scenario) {
            return response()->json(['errors' => __('messages.scenario.delete.error')], 422);
        }

        try {
            $scenario->delete();

            return response()->json([]);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }
    }

    /**
     * Подготовить формат списка схем сделок и статусов
     *
     * @param $programs
     * @return array
     */
    public function prepareStatesListByCrm($programs, $type)
    {
        $eventsList = [];
        foreach ($programs as $program) {
            $statuses = $type == 1 ? $program->states : $program->statuses;

            $states = [];
            if ($statuses) {
                foreach ($statuses as $state) {
                    $states[] = [
                        'id'   => $state->id,
                        'name' => $state->name
                    ];
                }
            }

            $eventsList[] = [
                'program_id'   => $program->id,
                'program_name' => $program->name,
                'states'       => $states
            ];
        }

        return $eventsList;
    }

    /**
     * Поиск сценария по id условия (для теста)
     *
     * @param $eventId
     * @return mixed
     */
    public function ifExistScenario($eventId)
    {
        $scenario = Scenario::with('events')->whereEventId($eventId)->get();

        return $scenario;
    }
}
