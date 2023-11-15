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

     //Dashboard
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'Dashboard'])->name('home');

  //Partners

    Route::get('partners', [App\Http\Controllers\Admin\PartnerController::class, 'PartnerView'])->name('partner');
    Route::post('partner-filter', [App\Http\Controllers\Admin\PartnerController::class, 'PartnerFilter'])->name('partner.filter');
    Route::post('save-partner', [App\Http\Controllers\Admin\PartnerController::class, 'SavePartner'])->name('save.partner');
    Route::get('delete-partner/{id}', [App\Http\Controllers\Admin\PartnerController::class, 'DeletePartner'])->name('delete.partner');
    Route::get('partner-status-change', [App\Http\Controllers\Admin\PartnerController::class, 'PartnerStatusChange'])->name('partner.status.change');
    Route::get('partner-detail/{id}', [App\Http\Controllers\Admin\PartnerController::class, 'ViewPartnerDetail'])->name('view.partner');
    Route::post('partner-multiplier-setting-save', [App\Http\Controllers\Admin\PartnerController::class, 'PartnerMultiplierSettingSave'])->name('partner.multiplier.setting.save');
    Route::post('partner-setting-save', [App\Http\Controllers\Admin\PartnerController::class, 'PartnerSettingSave'])->name('partner.setting.save');
    Route::get('sync-partner-products/{id}', [App\Http\Controllers\Admin\PartnerController::class, 'SyncProduct'])->name('sync.partner.products');



    //Products
    Route::get('products', [App\Http\Controllers\Admin\ProductController::class, 'Products'])->name('products');
    Route::get('product-view/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'ProductView'])->name('product.view');
    Route::get('shopify-create/{id}',[\App\Http\Controllers\Admin\ProductController::class,'CreateProductShopify']);
    Route::get('reject-product/{id}',[\App\Http\Controllers\Admin\ProductController::class,'RejectProduct']);
    Route::post('update-selected-products', [App\Http\Controllers\Admin\ProductController::class, 'UpdateSelectedProducts'])->name('update.selected.products');

    Route::get('approve-all',[\App\Http\Controllers\Admin\ProductController::class,'ApproveAll'])->name('approve.all');
    Route::get('deny-all',[\App\Http\Controllers\Admin\ProductController::class,'DenyAll'])->name('deny.all');




    Route::post('update-variant-detail',[\App\Http\Controllers\Admin\ProductController::class,'UpdateVariantDetail'])->name('update.variant.detail');

    Route::post('update-product-detail',[\App\Http\Controllers\Admin\ProductController::class,'UpdateProductDetail'])->name('update.product.detail');


    //Settings
    Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'Settings'])->name('settings');
    Route::post('setting-save', [App\Http\Controllers\Admin\SettingController::class, 'SettingsSave'])->name('setting.save');




});


Route::any('product-webhook', [App\Http\Controllers\Admin\ProductController::class, 'WebhookProductCreateUpdate']);
Route::any('delete-product-webhook', [App\Http\Controllers\Admin\ProductController::class, 'WebhookProductDelete']);



Route::get('check', [App\Http\Controllers\Admin\PartnerController::class, 'CheckPartnerWebhook']);


Route::get('/testing1', function() {

    $shop = \Illuminate\Support\Facades\Auth::user();
$shop=\App\Models\User::where('name','zain-store-tlx.myshopify.com')->first();

    $response = $shop->api()->rest('GET', '/admin/webhooks.json');
dd($response);
//    $response = $shop->api()->rest('delete', '/admin/api/webhooks/1091204317284.json');
    $orders = $shop->api()->rest('POST', '/admin/webhooks.json', [

        "webhook" => array(
            "topic" => "orders/delete",
            "format" => "json",
            "address" => env('APP_URL')."/webhook/order-delete"
        )
    ]);
    dd($orders);
    dd($response);
})->name('getwebbhook');


