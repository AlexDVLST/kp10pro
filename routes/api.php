<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:api')->group(function () {
    Route::domain('{account}.' . env('APP_DOMAIN'))->group(function () {
        
        //Store edited template
        Route::post('/editor/{id}/store', 'EditorController@store');
        //Cancel variant selection
        Route::post('/editor/{id}/cancel-variant-selection', 'OffersController@cancelVariantSelection');
        //Offer list
        Route::get('/offers/list/json', 'OffersController@listJson');
        //Copy offer
        Route::put('/offers/{id}/copy', 'OffersController@copy');
        //Products list
        Route::get('/products/list/json', 'ProductController@getProductListJson');

        //Get access token
        Route::get('/amocrm/users/{id}/token', 'AmocrmController@userAccessToken');
        //Get amoCRM deal offer
        Route::get('/amocrm/deals/{id}/offer', 'AmocrmController@dealOffer');
        //Save deal to offer
        Route::put('/amocrm/deals/{id}/offer', 'AmocrmController@setDealOffer');
        //Get megaplan deal offer
        Route::get('/megaplan/deals/{id}/offer', 'MegaplanController@dealOffer');
        //Save deal to offer
        Route::put('/megaplan/deals/{id}/offer', 'MegaplanController@setDealOffer');
        //Get bitrix24 deal offer
        Route::get('/bitrix24/deals/{id}/offer', 'Bitrix24Controller@dealOffer');
        //Save deal to offer
        Route::put('/bitrix24/deals/{id}/offer', 'Bitrix24Controller@setDealOffer');

    });
});
