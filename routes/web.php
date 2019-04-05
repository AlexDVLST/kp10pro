<?php

use Illuminate\Support\Facades\Cookie;
use App\Scopes\ClientScope;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::domain('{account}.' . env('APP_DOMAIN'))->group(function () {

    Route::middleware(['check.domain'])->group(function () {
        //After user registered
        Route::get('login-after-registered', 'Auth\LoginController@loginAfterRegistered');
        
        //Login form for subdomain
        Route::get('login', 'Auth\LoginController@showLoginForm');

        Route::middleware(['auth'])->group(function () { //, 'check.order'
            //home page for subdomain
            Route::get('/', function () {
                return redirect('/offers'); //
            });

            //Page File manager
            Route::get('/file-manager/', 'FileManagerController@index');
            //Upload new file
            Route::post('/file-manager/upload', 'FileManagerController@upload');
            //Upload cropped(optimized) file
            Route::post('/file-manager/upload/cropped', 'FileManagerController@uploadCropped');
            //Create folder
            Route::post('/file-manager/folder', 'FileManagerController@createFolder');
            //Rename folder
            Route::post('/file-manager/folder/rename', 'FileManagerController@renameFolder');
            //Delete folder
            Route::delete('/file-manager/folder', 'FileManagerController@deleteFolder');
            //Delete file
            Route::delete('/file-manager/file', 'FileManagerController@deleteFile');
            //Get folder/file list in JSON
            Route::get('/file-manager/json', 'FileManagerController@getStorageDataJson');
            //Move copied files
            Route::post('/file-manager/file/move', 'FileManagerController@moveFile');
            //Copy file
            Route::post('/file-manager/file/copy', 'FileManagerController@copyFile');
            //Rename file
            Route::post('/file-manager/file/rename', 'FileManagerController@renameFile');

            //Editor with grapesjs
            //Load assets(images) from DB
            Route::get('/editor/assets/load', 'EditorController@assetsLoad');
            //Get template data, products, assets
            Route::get('/editor/{id}', 'EditorController@index');
            //Get template by id in json for grapes.js framework
            Route::get('/editor/{id}/load', 'EditorController@load');
            //Store edited template
            Route::post('/editor/{id}/store', 'EditorController@store');
            //Storing new template based on edited
            Route::post('/editor/create-empty-offer', 'EditorController@createEmptyOffer');
            //Save offer html
            Route::post('/editor/html', 'EditorController@saveHtml');
            //Cancel variant selection
            Route::post('/editor/{id}/cancel-variant-selection', 'OffersController@cancelVariantSelection');

            //Home page
            Route::get('/home', 'HomeController@index');
            //Tour
            Route::post('/home/dismiss-tour', 'HomeController@dismissTour');

            // [ Offers ]
            //Get system offers status list
            Route::get('/offers/systemStates', 'OffersController@systemStates');
            //Отображение страницы "удалённые коммерческие предложения"
            Route::get('/settings/offers/removed', 'OffersController@getDeletedOffers');
            //Список удалённых коммерческих предложений. JSON
            Route::get('/settings/offers/removed/json', 'OffersController@getTrashedListJson');
            //Cписок коммерческих предложений. JSON
            Route::get('/offers/list/json', 'OffersController@listJson');
            //Коммерческие предложения resource
            Route::resource('/offers', 'OffersController');
            //Установка флага товара "Ком.предложение в корзине"
            Route::post('/offers/{id}/trash', 'OffersController@trash');
            Route::post('/offers/trash', 'OffersController@trashMultiple');

            Route::post('/offers/restore', 'OffersController@restoreMultiple');
            //Удаление флага товара "Товар в корзине"

            Route::post('/offers/{id}/restore', 'OffersController@restoreOffer');
            //Set offer state
            Route::put('/offers/{id}/state', 'OffersController@setState');
            //Copy offer
            Route::put('/offers/{id}/copy', 'OffersController@copy');
            //Update user template
            // Route::put('/offers/{id}/userTemplate', 'OffersController@setUserTemplate');
            //Remove integration deal relation
            Route::delete('/offers/{id}/integration/deal', 'OffersController@deleteIntegrationDeal');

            // [ Products ]
            Route::get('/products/excel/progress', 'ProductController@getProgressExcel');
            Route::get('/products/excel', 'ProductController@exportProducts');
            Route::post('/products/excel', 'ProductController@importProducts');
            Route::post('/products/excelCheck', 'ProductController@importProductsCheck');
            Route::get('/products/getusercollumns', 'ProductController@getVisibleProductCollumns');
            Route::post('/products/setusercollumns', 'ProductController@setVisibleProductCollumns'); //
            Route::post('/products/remove', 'ProductController@deletechecked'); //удаление товаров. Список.
            Route::get('/products/list/json', 'ProductController@getProductListJson');

            Route::resource('/products', 'ProductController'); //resource

            Route::post('/products/{id}/add-custom-field', 'ProductController@addcustomfield');
            // [ Product photos ]
            // Получить все фото данного товара
            Route::get('/products/{id}/file', 'ProductController@getProductFile');

            // [ Product custom fields ]
            Route::resource('/settings/product-custom-fields', 'ProductCustomFieldController');
            Route::get('product-custom-fields/list/json', 'ProductCustomFieldController@getProductDopFieldsListJson');

            //default page
            //Route::get('/{slug}', array('as' => 'page.show', 'uses' => 'PageController@show'));

            //Client
            Route::get('client/json', 'ClientController@listJson');
            Route::resource('client', 'ClientController');
            Route::get('client/{client}/json', 'ClientController@json');
            Route::post('client/{client}/email', 'ClientController@addEmail');

            //Settings
            Route::prefix('settings')->group(function () {
                //Employee
                //TODO-N: Звернути увагу
                Route::get('employee/{employee}/json', 'EmployeeController@json');
                Route::get('employee/json', 'EmployeeController@listJson');
                // Employee settings without permission
                Route::get('employee/{id}/edit', 'EmployeeController@edit');
                Route::put('employee/{employee}', 'EmployeeController@update');
                Route::post('employee/{employee}/change-password', 'EmployeeController@changePassword');
                // Route::resource('employee', 'EmployeeController');

                //Integration with email
                Route::post('integration/email/send', 'IntegrationEmailController@send');
                Route::resource('integration/email', 'IntegrationEmailController');
                
                Route::group(['middleware' => ['permission:view settings']], function () {
                    // [Currencies]
                    
                    Route::get('currencies/basic/json', 'CurrenciesController@basicDataJson'); // basic currencies
                    Route::get('currencies/list/json', 'CurrenciesController@listJson');
                    Route::get('currencies/{currency}/json', 'CurrenciesController@json');
                    
                    Route::resource('currencies', 'CurrenciesController'); // user currencies
                    
                    // Employee
                    // Route::get('employee/json', 'EmployeeController@listJson');
                    // Route::get('employee/{employee}/json', 'EmployeeController@json');
                    
                    // Employee settings with permission
                    Route::get('employee', 'EmployeeController@index');
                    Route::delete('employee/{employee}/{employeeForReplacement}', 'EmployeeController@destroy');
                    Route::post('employee', 'EmployeeController@store');
                    Route::get('employee/create', 'EmployeeController@create');
                    
                    Route::post('employee/{employee}/block', 'EmployeeController@block');
                    Route::post('employee/{employee}/unBlock', 'EmployeeController@unBlock');
                    Route::post('employee/importFromCRM', 'EmployeeController@importFromCRM');
                    Route::get('employee/getClientsOffersList/{id}', 'EmployeeController@getClientsOffersList');

                    //Role
                    Route::get('role/{role}/json', 'RoleController@json');
                    Route::put('role', 'RoleController@update');
                    Route::get('role/json', 'RoleController@listJson');
                    Route::resource('role', 'RoleController');

                    //Permission
                    Route::get('permission/json', 'PermissionController@listJson');
                    Route::put('permission/{permission}', 'PermissionController@update');

                    //IntegrationCrm
                    Route::get('integration/crm/json', 'IntegrationController@integration');
                    Route::post('integration/crm/megaplan/program', 'IntegrationController@addMegaplanProgram');
                    Route::delete('integration/crm/megaplan/{id}/program', 'IntegrationController@deleteMegaplanProgram');
                    //Add lead field amocrm
                    Route::post('integration/crm/amocrm/lead/field', 'IntegrationController@addAmocrmLeadField');
                    //Delete lead field
                    Route::delete('integration/crm/amocrm/lead/field/{id}', 'IntegrationController@deleteAmocrmLeadField');
                    //Add deal field bitrix24
                    Route::post('integration/crm/bitrix24/deal/field', 'IntegrationController@addBitrix24DealField');
                    //Delete deal field bitrix24
                    Route::delete('integration/crm/bitrix24/deal/field/{id}', 'IntegrationController@deleteBitrix24DealField');

                    //IntegrationCrm TODO: додати префікс CRM
                    Route::post('integration/set/cookies', 'IntegrationController@setCookies');

                    // Load Megaplan user
                    Route::get('integration/crm/megaplan/employees', 'MegaplanController@getEmployeesList');

                    Route::resource('integration/crm', 'IntegrationController');

                    //Add lead field amocrm
                    //Delete lead field amocrm
                    // Load AmoCrm user
                    Route::get('integration/crm/amocrm/employees', 'AmocrmController@getEmployeesList');

                    // Load Bitrix24 user
                    Route::get('integration/crm/bitrix24/employees', 'Bitrix24Controller@getEmployeesList');

                    // Route::resource('integration', 'IntegrationController');

                    Route::get('scenario/json', 'ScenarioController@listJson');
                    Route::get('scenario/get/events', 'ScenarioController@getEventsList');
                    Route::get('scenario/get/actions/{id}', 'ScenarioController@getActionsList');
                    Route::get('scenario/get/additional-actions/{id}', 'ScenarioController@getAdditionalActionsList');
                    Route::post('scenario/add', 'ScenarioController@addNewScenario');
                    Route::post('scenario/delete', 'ScenarioController@deleteScenario');
                    Route::resource('scenario', 'ScenarioController');
                });

                //Orders
                Route::get('order/json', 'OrderController@activeJson');
                Route::put('order', 'OrderController@createInvoice');
                Route::get('order/edit', 'OrderController@edit');
                Route::resource('order', 'OrderController');

                //Invoices
                Route::post('invoice/{invoice}/cancel', 'InvoiceController@cancel');
                Route::resource('invoice', 'InvoiceController');
            });

            //Megaplan api request
            Route::get('megaplan/programs', 'MegaplanController@programListJson');
            Route::get('megaplan/programs/{id}/field', 'MegaplanController@programFieldsJson');
            //amoCRM api request
            Route::get('amocrm/leads/fields', 'AmocrmController@leadsFields');
            //Bitrix24 api request
            Route::get('bitrix24/deal/fields', 'Bitrix24Controller@dealFields');
            
            //Change viewed of the notification
            Route::put('notification/{notification}/viewed', 'NotificationController@viewed');
            //UserMeta 
            Route::get('user/meta/{meta}', 'UserController@meta');
            Route::put('user/meta/{meta}', 'UserController@updateMeta');

            //Admin section routing
            Route::group(['middleware' => ['permission:admin help|admin offer']], function () {
                //Create namespace
                Route::namespace('Admin')->group(function () {
                    // Controllers Within The "App\Http\Controllers\Admin" Namespace
                    Route::prefix('admin')->group(function () {
                        Route::get('offers/json', 'OffersController@listJson');
                        Route::put('offers/{id}/upload', 'OffersController@upload');
                        Route::resource('offers', 'OffersController');
                        //Help section
                        Route::get('/help/json', 'HelpController@listJson');
                        Route::get('/help/section/json', 'HelpController@sectionListJson');
                        Route::resource('help', 'HelpController');
                    });
                });
            });
        });
    });
});

//Route for home page
Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    
    // App\Models\User::role('user')->each(function($user){
        // $accountId = $user->accountId;
        //Для оновлення account_id таблиці offers
        // App\Models\Offer::withoutGlobalScope(App\Scopes\OfferScope::class)->whereAccount($user->domain)->withTrashed()->get()->each(function($offer) use ($accountId){
        //     $offer->account_id = $accountId;
        //     $offer->save();
        // });
        
        //Для оновлення account_id таблиці currencies
        // App\Models\Currency::withoutGlobalScope(App\Scopes\CurrencyScope::class)->whereAccount($user->domain)->get()->each(function($currency) use ($accountId){
        //     $currency->account_id = $accountId;
        //     $currency->save();
        // }); 
        
        //Для оновлення account_id таблиці products
        // App\Models\Product::withoutGlobalScope(App\Scopes\ProductScope::class)->whereAccount($user->domain)->get()->each(function($product) use ($accountId){
        //     $product->account_id = $accountId;
        //     $product->save();
        // });

        //Для оновлення account_id таблиці product_custom_field
        // App\Models\ProductCustomField::withoutGlobalScope(App\Scopes\ProductCustomFieldScope::class)->whereAccount($user->domain)->get()->each(function($product) use ($accountId){
        //     $product->account_id = $accountId;
        //     $product->save();
        // });
        
        //Для оновлення account_id таблиці clients
        // App\Models\Client::withoutGlobalScope(App\Scopes\ClientScope::class)->whereAccount($user->domain)->withTrashed()->get()->each(function($client) use ($accountId){
        //     $client->account_id = $accountId;
        //     $client->save();
        // });

        //Для оновлення account_id таблиці orders
        // App\Models\Order::withoutGlobalScope(App\Scopes\OrderScope::class)->whereAccount($user->domain)->get()->each(function($order) use ($accountId){
        //     $order->account_id = $accountId;
        //     $order->save();
        // });
        
        //Для оновлення account_id таблиці files
        // App\Models\File::withoutGlobalScope(App\Scopes\FileScope::class)->whereAccount($user->domain)->get()->each(function($file) use ($accountId){
        //     $file->account_id = $accountId;
        //     $file->save();
        // });
    // });

    // Spatie\Permission\Models\Permission::create(['name' => 'admin offer']);
    // Spatie\Permission\Models\Permission::create(['name' => 'admin help']);

    // App\Models\User::whereId(2)->first()->givePermissionTo(['admin help']);

    // Illuminate\Support\Facades\Auth::loginUsingId(685);

});

Route::get('/help/video/json', 'HelpController@videos');

//Check if user is logged in
//May use for js checker
Route::get('/auth/check', function () {
    return response()->json(['auth' => Auth::check()]);
});

Auth::routes();

//Moneta
Route::post('/moneta/pay', 'MonetaController@pay');
Route::get('/moneta/success', 'MonetaController@success');
Route::get('/moneta/failure', 'MonetaController@failure');
Route::get('/moneta/payment', 'MonetaController@payment');
Route::get('/moneta/processing', 'MonetaController@processing');

//Short link for offers
Route::get('/{url}', function ($url) {
    if (Storage::exists('public/offers/' . $url . '/index.html')) {
        echo Storage::get('public/offers/' . $url . '/index.html');
    } else {
        abort(404);
    }
});

//Short link for export pdf and excel s
// Route::get('/{url}/pdf', 'OffersExportController@exportPdf');
// Route::get('/{url}/pdf/full', 'OffersExportController@exportPdfFull'); //2
Route::get('/{url}/excel', 'OffersExportController@exportExcel');
Route::get('/{url}/pdf', 'OffersExportController@pdf');
Route::get('/{url}/pdf/{full}', 'OffersExportController@pdf');

//Get offer json by url
Route::get('/{url}/json', 'OffersController@jsonByUrl');
Route::post('/{url}/variant', 'OffersController@selectVariant');

//Integration
Route::prefix('integration')->group(function () {
    // megaplan
    Route::any('megaplan/settings', 'MegaplanController@index');
    Route::any('megaplan/event', 'MegaplanController@events');
    Route::get('megaplan/programs', 'MegaplanController@programListJson');
//    Route::any('megaplan/indexz', 'MegaplanController@indexZ'); //todo AIM my test

    // amoCRM
    Route::put('amocrm/store', 'AmocrmController@store');
    Route::post('amocrm/events', 'AmocrmController@events');

    //bitrix24
    Route::any('bitrix24/', 'Bitrix24Controller@index');
    Route::any('bitrix24/events', 'Bitrix24Controller@events');
    Route::post('bitrix24/check', 'Bitrix24Controller@checkAccount');

    //Email integration
    //Change offer state if user opened email
    Route::get('email/offer/{id}/state', 'IntegrationEmailController@setEmailOfferState');

    //Tilda
    Route::post('tilda/register', 'IntegrationTilda@register');
});
