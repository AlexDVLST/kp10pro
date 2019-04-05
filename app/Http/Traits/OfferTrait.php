<?php

namespace App\Http\Traits;

use App\Http\Controllers\ScenarioController;
use App\Jobs\ScenarioJob;
use App\Models\OfferHistory;
use App\Models\User;
use App\Models\Client;
use App\Models\Offer;
use App\Models\SystemOfferState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\EditorController;
use App\Scopes\OfferScope;

trait OfferTrait
{
    public function copyOffer($user, $id, Request $request, $system = 0)
    {
        $request->validate([
            'name' => 'string'
        ]);

        $name       = $request->input('name');
        $isTemplate = (int)$request->input('isTemplate');

        $parentOffer = Offer::withoutGlobalScope(OfferScope::class)->with('variants.products.values', 'variants.fields', 'variants.specialDiscounts', 'currency')->whereId($id)->first();

        if (!$parentOffer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        //Currencies
        $currencies   = $this->getUserCurrencies($user);
        $baseCurrency = $currencies->where('basic', 1)->first();

        $offer = [];

        try {
            DB::transaction(function () use ($parentOffer, $name, $isTemplate, $user, &$offer, $baseCurrency, $system) {
                //Get latest offer number
                $latestNumber = Offer::latest()->first()->number;

                $offer                  = $parentOffer->replicate();
                $offer->system          = $system;
                $offer->offer_name      = $name;
                $offer->url             = EditorController::generateUrl(5);
                $offer->user_id         = $user->id;
                $offer->account_id      = $user->accountId;
                $offer->parent_offer_id = $parentOffer->id;

                $offer->save();
                //Get parent client id
                $clientId = $parentOffer->clientId;
                //Get parent contact person id
                $contactPersonId = $parentOffer->contactPersonId;

                //For template clear params
                if ($isTemplate) {
                    $clientId = $contactPersonId = 0;
                }

                //Save number after offer was saved
                $offer->numberRelation()
                    ->create(['number' => $latestNumber + 1]);

                //Create client relation
                $offer->clientRelation()
                    ->create(['client_id' => $clientId]);

                //Create contact person
                $offer->contactPersonRelation()
                    ->create(['client_id' => $contactPersonId]);

                //Create state
                $offer->state()
                    ->create(['state_id' => 1]);

                //Copy variants
                $parentOffer->variants->each(function ($parentVariant) use ($offer) {
                    $variant = $parentVariant->replicate();

                    $variant->offer_id = $offer->id;
                    $variant->save();

                    //Copy variant fields
                    $parentVariant->fields->each(function ($parentField) use ($variant) {
                        $field = $parentField->replicate();

                        $field->variant_id = $variant->id;
                        $field->save();
                    });

                    //Copy variant products
                    $parentVariant->products->each(function ($parentProduct) use ($variant) {
                        $product = $parentProduct->replicate();

                        $product->offer_id = $variant->offer_id;
                        $product->variant_id = $variant->id;
                        $product->save();

                        //Copy product values
                        $parentProduct->values->each(function ($parentValue) use ($product) {
                            $value = $parentValue->replicate();

                            $value->variant_product_id = $product->id;
                            $value->save();
                        });
                    });

                    //Copy special discount
                    $parentVariant->specialDiscounts->each(function ($parentSpecialDiscounts) use ($variant) {
                        $specialDiscounts = $parentSpecialDiscounts->replicate();

                        $specialDiscounts->variant_id = $variant->id;
                        $specialDiscounts->save();
                    });
                });

                //User template status
                if ($offer->userTemplate) {
                    //Update
                    $offer->userTemplate()
                        ->update(['is_template' => $isTemplate]);
                } else {
                    //Create
                    $offer->userTemplate()
                        ->create(['is_template' => $isTemplate]);
                }

                //Template information
                $offer->template()
                    ->create([
                        'name'    => $parentOffer->template->name,
                        'version' => $parentOffer->template->version,
                    ]);

                //Create employee of the template
                $offer->employee()
                    ->create(['user_id' => $user->id]);

                //Currency
                if ($baseCurrency) {
                    //For system create base currency
                    if ($parentOffer->system || !$parentOffer->currency) {
                        $offer->currency()
                            ->create([
                                'currency_id' => $baseCurrency->id
                            ]);
                    } else {
                        $offer->currency()
                            ->create([
                                'currency_id' => $parentOffer->currency->currency_id
                            ]);
                    }
                }

                try {
                    //Copy html template
                    if (Storage::exists('public/offers/' . $parentOffer->url . '/index.html')) {
                        Storage::copy('public/offers/' . $parentOffer->url . '/index.html', 'public/offers/' . $offer->url . '/index.html');
                    }
                } catch (Exception $e) {
                    return response()->json(['errors' => $e->getMessage()], 422);
                }
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return $offer;
    }

    /**
     * Set offer state
     *
     * @param $id
     * @param $stateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function setOfferState($id, $stateId)
    {
        $offer = Offer::whereId($id)->with('state')->first();
        $state = SystemOfferState::whereId($stateId)->first();

        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }
        if (!$state) {
            return response()->json(['errors' => __('messages.state.not_found')], 422);
        }

        try {
            //Change state
            $offer->state()->update(['state_id' => $stateId]);
            //Save status history
            $offer->stateHistory()->create(['state_id' => $stateId, 'user_id' => $offer->user_id]);

            $user = Auth::user();
            //Для сценария (Изменился статус КП)
            ScenarioJob::dispatch(9, 5, $user, $offer, 0);
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.offer.save.success')]);
    }
}
