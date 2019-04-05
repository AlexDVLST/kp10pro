<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferVariant;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App;

class OffersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $page = Page::whereSlug('admin-offers')->first();

        // $this->import('company', 1658);

        return view('admin.offers');
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
     * Uploading selected template to remove server
     *
     * @param string $account
     * @param int $id
     * @return json
     */
    public function upload($account, $id)
    {
        //Connect to remote DB
        if (App::environment('local')) {
            $db = DB::connection('remote-mysql'); //
        } else {
            //On remote server connect to localhost
            $db = DB::connection('mysql'); //
        }
        
        //Update template for user
        $parentOffer = Offer::whereId($id)->with('template', 'variants.products.values', 'variants.fields')->first();

        if (!$parentOffer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        $offer = $parentOffer->replicate();

        //TODO: ЗАМІНИТИ НА ACCOUNTI
        if (App::environment('local')) {
            $users = $db->select('SELECT domain FROM users WHERE id = 1 GROUP BY `domain`');
        } else {
            //On remote server connect to localhost
            $users = $db->select('SELECT domain FROM users WHERE id != 1 GROUP BY `domain`');
        }

        if (is_array($users)) {
            DB::transaction(function () use ($db, $users, $parentOffer, $offer) {
                foreach ($users as $user) {
                    try {
                        $offerRemote = $db->table('offers')
                            ->select('offers.id')
                            ->join('offer_templates', 'offers.id', '=', 'offer_templates.offer_id')
                            ->where([
                                ['account', '=', $user->domain], //TODO: ЗАМІНИТИ НА ACCOUNTI
                                ['offer_templates.name', '=', $parentOffer->template->name],
                                ['system', '=', 1],
                            ])->first();

                        //If offer exist
                        if ($offerRemote) {
                            $db->table('offers')
                                ->join('offer_templates', 'offers.id', '=', 'offer_templates.offer_id')
                                ->where([
                                    ['offers.id', '=', $offerRemote->id]
                                ])->update([
                                    'offers.gjs_components'   => $offer->gjs_components,
                                    'offers.gjs_css'          => $offer->gjs_css,
                                    'offers.gjs_html'         => $offer->gjs_html,
                                    'offers.gjs_styles'       => $offer->gjs_styles,
                                    'offers.updated_at'       => Carbon::now(),
                                    'offer_templates.version' => $parentOffer->template->version
                                ]);

                            $variants = $db->table('offer_variants')
                                    ->where('offer_id', '=', $offerRemote->id)
                                    ->get();

                            if ($variants) {
                                $variants->each(function ($variant, $index) use ($parentOffer, $db, $offerRemote) {
                                    $parentVariant =  $parentOffer->variants->filter(function ($parentVariant, $pIndex) use ($index) {
                                        return $pIndex === $index;
                                    })->first();

                                    if ($parentVariant) {
                                        $db->table('offer_variants')
                                            ->where('id', '=', $variant->id)
                                            ->update([
                                                'price' => $parentVariant->price,
                                                'type'  => $parentVariant->type
                                            ]);

                                        //Create variant fields
                                        $variantFields = $db->table('offer_variant_fields')
                                            ->where('variant_id', '=', $variant->id)
                                            ->get();
                                        if ($variantFields->isEmpty()) {
                                            $parentVariant->fields->each(function ($parentField) use ($variant, $db, $offerRemote) {
                                                $db->table('offer_variant_fields')
                                                    ->insert([
                                                        'variant_id' => $variant->id,
                                                        'name'       => $parentField->name,
                                                        'index'      => $parentField->index,
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now(),
                                                        'type'       => $parentField->type,
                                                    ]);
                                            });
                                        }

                                        //Create variant products
                                        $variantProducts = $db->table('offer_variant_products')
                                            ->where('variant_id', '=', $variant->id)
                                            ->get();

                                        if (!$variantProducts->isEmpty()) {
                                            $variantProducts->each(function ($product, $index) use ($parentVariant, $db) {
                                                $parentProduct =  $parentVariant->products->filter(function ($parentProduct, $pIndex) use ($index) {
                                                    return $pIndex === $index;
                                                })->first();

                                                if ($parentProduct) {
                                                    //Update product data
                                                    $db->table('offer_variant_products')
                                                        ->where('id', '=', $product->id)
                                                        ->update([
                                                            'image'           => $parentProduct->image,
                                                            'description'     => $parentProduct->description,
                                                            'updated_at'      => Carbon::now(),
                                                        ]);
                                                    //Get product values
                                                    $productValues = $db->table('offer_variant_product_values')
                                                        ->where('variant_product_id', '=', $product->id)
                                                        ->get();

                                                    if ($productValues) {
                                                        $productValues->each(function ($productValue, $index) use ($parentProduct, $db) {
                                                            $parentValue = $parentProduct->values->filter(function ($parentValue, $pIndex) use ($index) {
                                                                return $pIndex === $index;
                                                            })->first();

                                                            if ($parentValue) {
                                                                $db->table('offer_variant_product_values')
                                                                    ->where('id', '=', $productValue->id)
                                                                    ->update([
                                                                        'value'              => $parentValue->value,
                                                                        'index'              => $parentValue->index,
                                                                        'type'               => $parentValue->type,
                                                                        'value_in_price'     => $parentValue->value_in_price,
                                                                        'updated_at'         => Carbon::now(),
                                                                        ]);
                                                            }
                                                        });
                                                    }
                                                }
                                            });
                                        } else {
                                            $parentVariant->products->each(function ($parentProduct) use ($variant, $db, $offerRemote) {
                                                $id = $db->table('offer_variant_products')
                                                    ->insertGetId([
                                                        'offer_id'        => $offerRemote->id,
                                                        'variant_id'      => $variant->id,
                                                        'product_id'      => 0,
                                                        'fake_product_id' => $parentProduct->fake_product_id,
                                                        'image'           => $parentProduct->image,
                                                        'group'           => $parentProduct->group,
                                                        'index'           => $parentProduct->index,
                                                        'description'     => $parentProduct->description,
                                                        'created_at'      => Carbon::now(),
                                                        'updated_at'      => Carbon::now(),
                                                    ]);

                                                //Copy product values
                                                $parentProduct->values->each(function ($parentValue) use ($db, $id) {
                                                    $db->table('offer_variant_product_values')
                                                        ->insert([
                                                            'variant_product_id' => $id,
                                                            'value'              => $parentValue->value,
                                                            'index'              => $parentValue->index,
                                                            'type'               => $parentValue->type,
                                                            'value_in_price'     => $parentValue->value_in_price,
                                                            'created_at'         => Carbon::now(),
                                                            'updated_at'         => Carbon::now(),
                                                            ]);
                                                });
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    } catch (Exception $e) {
                        return response()->json(['errors' => $e->getMessage()], 422);
                    }
                }
            });
        }

        return response()->json(['message' => __('messages.admin.offer.upload.success')]);
    }

    public function import($account, $id)
    {
        //Connect to remote DB
        $db = DB::connection('remote-mysql'); //

        $remoteOffer = $db->select("SELECT * FROM offers WHERE id = $id");

        if (empty($remoteOffer)) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        $remoteOffer = $remoteOffer[0];

        DB::transaction(function () use ($db, $remoteOffer) {
            $user = Auth::user();
            //Get latest offer number
            $latestNumber = Offer::latest()->first()->number;

            $remoteOfferTemplate = $db->select("SELECT * FROM offer_templates WHERE offer_id = {$remoteOffer->id}")[0];
            $remoteOfferVariants = $db->select("SELECT * FROM offer_variants WHERE offer_id = {$remoteOffer->id}");

            $offer = Offer::create([
                'gjs_assets'      => $remoteOffer->gjs_assets,
                'gjs_components'  => $remoteOffer->gjs_components,
                'gjs_css'         => $remoteOffer->gjs_css,
                'gjs_html'        => $remoteOffer->gjs_html,
                'gjs_styles'      => $remoteOffer->gjs_styles,
                'offer_name'      => $remoteOffer->offer_name,
                'user_id'         => $user->id,
                'created_at'      => $remoteOffer->created_at,
                'updated_at'      => $remoteOffer->updated_at,
                'url'             => $remoteOffer->url,
                'system'          => $remoteOffer->system,
                'parent_offer_id' => $remoteOffer->parent_offer_id,
                'account_id'      => $user->accountId,
                'deleted_at'      => $remoteOffer->deleted_at,
            ]);

            $offer = Offer::whereId($offer->id)->first();

            //Variants
            foreach ($remoteOfferVariants as $variant) {
                // //Fields
                // $remoteOfferVariantFields = $db->select("SELECT * FROM offer_variant_fields WHERE variant_id = {$variant->id}");
                // //Products
                // $remoteOfferVariantProducts = $db->select("SELECT * FROM offer_variant_products WHERE variant_id = {$variant->id}");

                // if(is_array($remoteOfferVariantProducts) && !empty($remoteOfferVariantProducts)){
                //     foreach ($remoteOfferVariantProducts as $product) {
                //         //Values
                //         $remoteOfferVariantProducts = $db->select("SELECT * FROM offer_variant_product_values WHERE variant_product_id = {$product->id}");

                //         dd($remoteOfferVariantProducts);
                //     }
                // }
                // dd($remoteOfferVariantProducts);

                OfferVariant::create([
                        'offer_id'   => $offer->id,
                        'name'       => $variant->name,
                        'price'      => $variant->price,
                        'created_at' => $variant->created_at,
                        'updated_at' => $variant->updated_at,
                        'selected'   => $variant->selected,
                        'active'     => $variant->active,
                        'tax'        => $variant->tax,
                        'type'       => $variant->type,
                    ]);
            }

            //Save number after offer was saved
            $offer->numberRelation()
                ->create(['number' => $latestNumber + 1]);

            //Create client relation
            $offer->clientRelation()
                ->create(['client_id' => 0]);

            //Create contact person
            $offer->contactPersonRelation()
                ->create(['client_id' => 0]);

            //Create state
            $offer->state()
                ->create(['state_id' => 1]);

            //Create
            $offer->userTemplate()
                 ->create(['is_template' => 0]);

            //Template information
            $offer->template()
                ->create([
                    'name'    => $remoteOfferTemplate->name,
                    'version' => $remoteOfferTemplate->version,
                ]);

            //Create employee of the template
            $offer->employee()
                ->create(['user_id' => $user->id]);

            //Currency
            $offer->currency()
                ->create(['currency_id' => 0]);
        });
    }

    /**
     * Get system offers json
     *
     * @return json
     */
    public function listJson()
    {
        //System offers/Base templates list
        // $systemOffers = [1];

        $offers = Offer::with('template')
            ->select(
                'offers.id',
                'offers.offer_name',
                'offers.created_at',
                'offers.updated_at',
                'offers.url'
            )
            ->whereSystem(1)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($offers);
    }
}
