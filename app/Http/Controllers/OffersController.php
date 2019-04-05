<?php

namespace App\Http\Controllers;

use App\Jobs\ScenarioJob;
use App\Models\IntegrationBitrix24;
use App\Models\OfferHistory;
use App\Models\Page;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\SystemOfferState;
use App\Models\OfferStateHistory;
use App\Models\Integration;
use App\Http\Traits\CurrencyTrait;
use App\Http\Traits\OfferTrait;
use App\Scopes\OfferScope;
use App\Helpers\Amocrm;
use App\Models\IntegrationMegaplan;
use App\Models\IntegrationAmocrm;

class OffersController extends Controller
{
    use CurrencyTrait, OfferTrait;

    public function __construct()
    {
        //Permissions
        $this->middleware(['permission:view offer|view-own offer'], ['except' => ['jsonByUrl', 'selectVariant']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($account, Request $request)
    {
        $page = Page::whereSlug('offers')->first();
        $user = Auth::user();
        // $offer = $this->copyOffer($user, 640, new Request(['name' => 'Базовый']), 1);
        // User::whereId()->first();

        // $permissions = $user->getAllPermissions()->toArray();

        // dd( $permissions);
        // dd( print_r($permissions,1));

        return view(
            'pages/offers',
            ['page' => $page, 'user' => $user]
        );
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
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id = 'home')
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
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
     *
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
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($account, $id)
    {
        $offer = Offer::withTrashed()->whereId($id)->first();

        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        try {
            $offer->forceDelete();
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.offer.destroy.success')]);
    }

    /**
     * @param $account
     * @param Request $request
     *
     * @return array
     */
    public function listJson($account, Request $request)
    {
        $search   = $request->input('search');
        $orderby  = $request->input('orderby') ? $request->input('orderby') : 'created_at';
        $order    = $request->input('order') ? $request->input('order') : 'desc';
        $with     = $request->input('with');

        //Get integration settings
        $integration = Integration::first();
        $integrationCrm = '';

        if ($integration) {
            if ($integration->system_crm_id == 1) { //Megaplan
                $integrationCrm = IntegrationMegaplan::first();
            }
            if ($integration->system_crm_id == 2) { //Amocrm
                $integrationCrm = IntegrationAmocrm::first();
            }
            if ($integration->system_crm_id == 3) { //Bitrix
                $integrationCrm = IntegrationBitrix24::first();
            }
        }

        $query = Offer::query()
            ->with(
                'clientRelation.client',
                'contactPersonRelation.client',
                'user',
                'employee.user',
                'state.data',
                'variants',
                'numberRelation',
                'template',
                'currency.data.system',
                'amocrmDeal.data.fields.values',
                'megaplanDeal.values.field'
            )
            ->select(
                'offers.id',
                'offers.offer_name',
                'offers.created_at',
                'offers.updated_at',
                'offers.url',
                'offers.user_id',
                'offers.system',
                // 'offers.template',
                'offer_user_templates.is_template'
            )
            ->leftJoin('offer_user_templates', 'offers.id', '=', 'offer_user_templates.offer_id')
            ->orderBy('system', 'desc')
            ->orderBy('offer_user_templates.is_template', 'desc')
            ->orderBy($orderby, $order);

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('offer_name', 'like', '%' . $search . '%')
                    ->orWhere('url', 'like', '%' . $search . '%');
            });
        }

        //Cheking Permissions in OfferScope.php
        $offers = $query->paginate(20);

        if ($offers) {
            foreach ($offers as $key => $value) {
                $value->created_at_formatted = $value->created_at->format('H:i j.m.Y');
                $value->updated_at_formatted = $value->updated_at->format('H:i j.m.Y');
                //update value from relation
                $value->number = $value->number;

                if ($value->clientRelation && $value->clientRelation->client) {
                    $value->clientRelation->client->displayName = $value->clientRelation->client->displayName;
                }
                if ($value->contactPersonRelation && $value->contactPersonRelation->client) {
                    $value->contactPersonRelation->client->displayName = $value->contactPersonRelation->client->displayName;
                }
                //TODO: може виникати помилка якщо юзера видалили !!
                $value->user->displayName  = $value->user->displayName;
                $value->isUserTemplate     = $value->is_template;

                //Create link to deal card
                if ($value->amocrmDeal && $integrationCrm) {
                    $value->dealCardLink = 'https://' . $integrationCrm->host . '/leads/detail/' . $value->amocrmDeal->deal_id;
                }
                if ($value->megaplanDeal && $integrationCrm) {
                    $value->dealCardLink = 'https://' . $integrationCrm->host . '/deals/' . $value->megaplanDeal->deal_id . '/card';
                }
                //TODO: Bitrix
            }
        }

        $response = [
            'pagination' => [
                'total'        => $offers->total(),
                'per_page'     => $offers->perPage(),
                'current_page' => $offers->currentPage(),
                'last_page'    => $offers->lastPage(),
                'from'         => $offers->firstItem(),
                'to'           => $offers->lastItem()
            ],
            'data'       => $offers
        ];

        return $response;
    }

    /**
     * Move to trash
     *
     * @param string $account
     * @param int $id
     * @param Request $request
     * @return json
     */
    public function trash($account, $id, Request $request)
    {
        $user = Auth::user();

        $offer = Offer::with('amocrmDeal', 'megaplanDeal')->whereId($id)->first();

        // if ($user->can('delete offer') || ($user->can('delete-own offer') && $user->id == $offer->user_id)) {
        if ($user->userCan('delete offer') || ($user->userCan('delete-own offer') && $user->id == $offer->user_id)) {
            if (!$offer) {
                return response()->json(['errors' => __('messages.offer.not_found')], 422);
            }

            try {
                $offer->delete();
                //Delete integration relation
                if ($offer->amocrmDeal) {
                    $offer->amocrmDeal->delete();
                }
                if ($offer->megaplanDeal) {
                    $offer->megaplanDeal->delete();
                }
            } catch (Exception $e) {
                return response()->json(['errors' => $e->getMessage()], 422);
            }
        } else {
            return response()->json(['errors' => __('messages.permission.denied')], 422);
        }

        return response()->json(['message' => __('messages.offer.trash.success')]);
    }

    /**
     * Remove multiple offers
     *
     * @param string $account
     * @param Request $request
     * @return json
     */
    public function trashMultiple($account, Request $request)
    {
        $request->validate([
            '*' => 'numeric'
        ]);

        $user     = Auth::user();
        $offersId = $request->all();

        $offers      = Offer::whereIn('id', $offersId)->get();
        $deleteOffer = $user->userCan('delete offer');
        $deleteOwn   = $user->userCan('delete-own offer');
        try {
            DB::beginTransaction();
            $offers->each(function ($offer) use($user, $deleteOffer, $deleteOwn) {
                if ($deleteOffer || ($deleteOwn && $user->id == $offer->user_id)) {
                    if (!$offer->system) {
                        $offer->delete();
                    }
                } else {
                    return response()->json(['errors' => __('messages.permission.denied')], 422);
                }

            });

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.offer.trash.success')]);
    }

    /**
     * Get trashed
     *
     * @param [type] $account
     * @return void
     */
    public function getDeletedOffers($account)
    {
        $page = Page::whereSlug('offers-removed')->first();
        $user = Auth::user();

        return view(
            'pages/offers-removed',
            ['page' => $page, 'user' => $user]
        );
    }

    /**
     * Get Trashed offer json
     *
     * @param string $account
     * @param Request $request
     * @return json
     */
    public function getTrashedListJson($account, Request $request)
    {
        $search   = $request->input('search');
        $orderby  = $request->input('orderby') ? $request->input('orderby') : 'created_at';
        $order    = $request->input('order') ? $request->input('order') : 'desc';

        $query = Offer::query()
            ->select(
                'offers.id',
                'offers.offer_name',
                'offers.created_at',
                'offers.deleted_at',
                'offers.url',
                'offers.user_id'
            )
            ->onlyTrashed()
            ->with('employee.user')
            ->orderBy('system', 'desc')
            ->orderBy($orderby, $order);

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('offer_name', 'like', '%' . $search . '%')
                      ->orWhere('url', 'like', '%' . $search . '%');
            });
        }

        $offers = $query->paginate(20);

        $response = [
            'pagination' => [
                'total'        => $offers->total(),
                'per_page'     => $offers->perPage(),
                'current_page' => $offers->currentPage(),
                'last_page'    => $offers->lastPage(),
                'from'         => $offers->firstItem(),
                'to'           => $offers->lastItem()
            ],
            'data'       => $offers
        ];

        return $response;
    }

    /**
     * Restore trashed offer
     *
     * @param string $account
     * @param int $id
     * @return json
     */
    public function restoreOffer($account, $id)
    {
        $user  = Auth::user();
        $offer = Offer::withTrashed()->whereId($id)->first();

        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        try {
            $offer->restore();
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.offer.restored.success')]);
    }

    /**
     * @param $account
     * @param Request $offers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restoreMultiple($account, Request $offers)
    {
        $user       = Auth::user();
        $offersList = $offers->all();
        $offers     = Offer::withTrashed()->whereIn('id', $offersList)->get();
        // Использу ту же роль. Может удалять - может восстанавливать
        // if ($user->can('delete offer') || ($user->can('delete-own offer'))) {
        if ($user->userCan('delete offer') || ($user->userCan('delete-own offer'))) {
            try {
                DB::beginTransaction();
                $offers->each(function ($offer) {
                    $offer->restore();
                });
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                return response()->json(['errors' => $e->getMessage()], 422);
            }
        } else {
            return response()->json(['errors' => __('messages.permission.denied')], 422);
        }
        return response()->json(['message' => __('messages.offer.restore.success')]);
    }

    /**
     * Get all offer states
     *
     * @return json
     */
    public function systemStates()
    {
        return response()
            ->json(SystemOfferState::all());
    }

    /**
     * Change offer state
     *
     * @param string $account
     * @param int $id
     * @param Request $request
     * @return void
     */
    public function setState($account, $id, Request $request)
    {
        return $this->setOfferState($id, $request->get('stateId'));
    }

    /**
     * Copy offer to new one
     *
     * @param string $account
     * @param int $id
     * @return void
     */
    public function copy($account, $id, Request $request)
    {
        $user = Auth::user();

        //Check permission
        if (!$user->userCan('create offer')) {
            return response()->json(['errors' => __('messages.permission.denied')], 422);
        }

        $offer = $this->copyOffer($user, $id, $request);

        if ($offer) {
            unset($offer->gjs_components, $offer->gjs_css, $offer->gjs_html, $offer->gjs_styles, $offer->gjs_assets);
        }

        return response()->json(['message' => __('messages.offer.copy.success'), 'offer' => $offer]);
    }

    /**
     * Get offer json by url
     *
     * @param string $account
     * @param string $url
     * @return json
     */
    public function jsonByUrl($url)
    {
        $offer = Offer::withoutGlobalScope(OfferScope::class)->with(
                'variants.products.values',
                'variants.fields',
                'variants.specialDiscounts',
                'numberRelation',
                'currency.data',
                'amocrmDeal.data.fields.values',
                'amocrmDeal.data.fields.customField',
                'megaplanDeal.values.field'
            )
            ->select(
                'offers.id',
                'offers.offer_name',
                'offers.created_at',
                'offers.updated_at',
                'offers.url'
                // 'offers.template'
            )->whereUrl($url)->first();

        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        //Format dates
        $offer->created_at_formatted = $offer->created_at->format('j.m.y');
        $offer->updated_at_formatted = $offer->updated_at->format('H:i j.m.y');
        $offer->number               = $offer->number;

        return response()->json($offer);
    }

    /**
     * Select variant
     *
     * @param string $url
     * @param Request $request
     * @return json
     */
    public function selectVariant($url, Request $request)
    {
        $request->validate([
            'index' => 'required|numeric'
        ]);

        $offer = Offer::with('variants', 'clientRelation')
            ->select(
                'offers.id',
                'offers.offer_name',
                'offers.created_at',
                'offers.updated_at',
                'offers.url',
                'offers.account_id'
                // 'offers.template'
            )->whereUrl($url)->first();

        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        $index = $request->input('index');

        //Check selected variant
        $variant = $offer->variants->filter(function ($variant) {
            return $variant->selected == 1;
        })->first();

        if ($variant) {
            //Show error. Variant already selected
            return response()->json(['errors' => __('messages.offer.variant.already_selected')], 422);
        }

        //Find variant by index
        $variant = $offer->variants->filter(function ($variant, $key) use ($index) {
            return $key == $index;
        })->first();

        if ($variant) {
            try {
                $variant->selected = 1;
                $variant->save();

                $user = Auth::user();
                $clientId = 0;
                //предварительно клиент иницировал выбор варианта
                if(!$user){
                    $clientId = $offer->clientRelation->client_id;
                }

                ScenarioJob::dispatch(5, 3, $user, $offer, $clientId);

            } catch (Exception $e) {
                return response()->json(['errors' => $e->getMessage()], 422);
            }

            return response()->json(['message' => __('messages.offer.variant.selected')]);
        }

        return response()->json(['errors' => __('messages.offer.variant.not_found')], 422);
    }

    /**
     * Cancel variant selection
     *
     * @param string $account
     * @param int $id
     * @return boolean
     */
    public function cancelVariantSelection($account, $id)
    {
        $offer = Offer::with('variants')->whereId($id)->first();

        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        //Find selected variant
        $variant = $offer->variants->filter(function ($variant) {
            return $variant->selected == 1;
        })->first();

        if ($variant) {
            try {
                $variant->selected = 0;
                $variant->save();
            } catch (Exception $e) {
                return response()->json(['errors' => $e->getMessage()], 422);
            }

            return response()->json(['message' => __('messages.offer.variant.cancel.success')]);
        }

        return response()->json(['errors' => __('messages.offer.variant.cancel.error')], 422);
    }

    /**
     * Delete integration deal relation
     *
     * @param [type] $account
     * @param [type] $id
     * @return void
     */
    public function deleteIntegrationDeal($account, $id)
    {
        $offer = Offer::with('amocrmDeal', 'megaplanDeal')->whereId($id)->first();

        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }
        try {
            if ($offer->megaplanDeal) {
                $offer->megaplanDeal->delete();
            }
            if ($offer->amocrmDeal) {
                $offer->amocrmDeal->delete();
            }
            //TODO: Bitrix

        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.offer.integration.deal.relation.delete.success')]);
    }
}
