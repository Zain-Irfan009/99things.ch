<?php

use Illuminate\Support\Facades\Route;

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


Route::group(['middleware' => ['auth.shopify']], function () {

    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'Dashboard'])->name('home');
    Route::get('partners', [App\Http\Controllers\Admin\PartnerController::class, 'PartnerView'])->name('partner');
    Route::post('save-partner', [App\Http\Controllers\Admin\PartnerController::class, 'SavePartner'])->name('save.partner');


});

//Route::get('/testing1', function() {
//
//    $shop = \Illuminate\Support\Facades\Auth::user();
//$shop=\App\Models\User::where('name','awakewater-earth.myshopify.com')->first();
////$shop=\App\Models\User::where('name','prod-awake-water.myshopify.com')->first();
//    $response = $shop->api()->rest('GET', '/admin/webhooks.json');
//dd($response);
////    $response = $shop->api()->rest('delete', '/admin/api/webhooks/1091204317284.json');
//    $orders = $shop->api()->rest('POST', '/admin/webhooks.json', [
//
//        "webhook" => array(
//            "topic" => "orders/delete",
//            "format" => "json",
//            "address" => env('APP_URL')."/webhook/order-delete"
//        )
//    ]);
//    dd($orders);
//    dd($response);
//})->name('getwebbhook');


