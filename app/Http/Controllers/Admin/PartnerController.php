<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Jobs\PartnerProductsSyncJob;
use App\Models\Log;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Osiset\BasicShopifyAPI\BasicShopifyAPI;
use Osiset\BasicShopifyAPI\Options;
use Osiset\BasicShopifyAPI\Session;

class PartnerController extends BaseController
{

    public function PartnerView(Request $request){

        $partners=Partner::query();
        if ($request->search != "") {
           $partners->where('name', 'like', '%' . $request->search . '%')->orWhere('shop_name', 'like', '%' . $request->search . '%');
        }
        if($request->platform != ""){
            $partners->where('platform' , $request->platform);
        }
        if($request->status!=""){
            $partners->where('status' , $request->status);
        }

        $partners=$partners->orderBy('id','desc')->paginate(20)->appends($request->all());
    $languages= DB::table('languages')->get();
        return view('admin.partners.index',compact('partners','languages'));
    }


    public function SavePartner(Request $request){

        try {
            if($request->platform=='Shopify'){


            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make('12345678');
            $user->save();

            $partner = new Partner();
            $partner->name = $request->name;
            $partner->email = $request->email;
            $partner->shop_name = $request->shop_name;
            $partner->shopify_token = $request->shopify_token;
            $partner->api_key = $request->api_key;
            $partner->api_secret = $request->api_secret;
            $partner->platform = $request->platform;
            $partner->store_language_id = $request->store_language;
            $partner->user_id = $user->id;
            $partner->save();

            $user->partner_id = $partner->id;
            $user->save();



            $webhookcreate = [
                "webhook" => [
                    "topic" => "products/create",
                    "address" => env('APP_URL') . "/product-webhook",
                    "format" => "json",
                ]
            ];

            $webhookupdate = [
                "webhook" => [
                    "topic" => "products/update",
                    "address" => env('APP_URL') . "/product-webhook",
                    "format" => "json",
                ]
            ];

            $webhookdelete = [
                "webhook" => [
                    "topic" => "products/delete",
                    "address" => env('APP_URL') . "/delete-product-webhook",
                    "format" => "json",
                ]
            ];


            $response_create = $this->api($partner)->rest('POST', '/admin/api/webhooks.json', $webhookcreate);
            $response_create = json_decode(json_encode($response_create));

            $response_update = $this->api($partner)->rest('POST', '/admin/api/webhooks.json', $webhookupdate);
            $response_update = json_decode(json_encode($response_update));

            $response_delete = $this->api($partner)->rest('POST', '/admin/api/webhooks.json', $webhookdelete);
            $response_delete = json_decode(json_encode($response_delete));

            if ($response_create->errors == false && $response_update->errors == false && $response_delete->errors == false) {

                $partner->webhook_product_create_id = $response_create->body->webhook->id;
                $partner->webhook_product_update_id = $response_update->body->webhook->id;
                $partner->webhook_product_delete_id = $response_delete->body->webhook->id;
                $partner->save();

                PartnerProductsSyncJob::dispatch($partner->id);
            }

            return back()->with('success', 'Partner Details Save Successfully');
                }else{
                return back()->with('error', 'This Platform is coming soon. Stay tuned!');
            }
        }catch (\Exception $exception){

            $partner->delete();
         $user->forceDelete();
            return back()->with('error', 'Partner Details is Wrong!');
        }
    }


    public function CheckPartnerWebhook(){
        $partner=Partner::first();
//        $delete_create_webhook = $this->api($partner)->rest('delete', '/admin/api/webhooks/1421926826258.json');
//dd($delete_create_webhook);
        $response = $this->api($partner)->rest('get', '/admin/api/webhooks.json');
dd($response);
    }


    public function DeletePartner($id){

        $partner=Partner::find($id);

        try {
            $delete_create_webhook = $this->api($partner)->rest('delete', '/admin/api/webhooks/' . $partner->webhook_product_create_id . '.json');
            $delete_update_webhook = $this->api($partner)->rest('delete', '/admin/api/webhooks/' . $partner->webhook_product_update_id . '.json');
            $delete_webhook = $this->api($partner)->rest('delete', '/admin/api/webhooks/' . $partner->webhook_product_delete_id . '.json');

            $delete_create_webhook = json_decode(json_encode($delete_create_webhook));

            $delete_update_webhook = json_decode(json_encode($delete_update_webhook));
            $delete_webhook = json_decode(json_encode($delete_webhook));

//            if ($delete_create_webhook->errors == false && $delete_update_webhook->errors == false && $delete_webhook->errors == false) {
            ProductImage::where('partner_id',$id)->delete();
            ProductVariant::where('partner_id',$id)->delete();
            Product::where('partner_id',$id)->delete();
            User::where('partner_id', $id)->forceDelete();
                Partner::where('id', $id)->delete();

//            }
            return back()->with('success', 'Partner Delete Successfully');
        }catch (\Exception $exception){

            User::where('partner_id', $id)->forceDelete();
            Partner::where('id', $id)->delete();
            return back()->with('success', 'Partner Delete Successfully');
        }
    }


    public function PartnerFilter(Request $request){

        $partners=Partner::query();
        if($request->partner_filter) {
            $partners = $partners->where('name', 'like', '%' . $request->partner_filter . '%')->orWhere('shop_name', 'like', '%' . $request->partner_filter . '%');
        }
        if($request->platform) {
            $partners = $partners->where('platform', $request->platform);
        }
        $partners=$partners->paginate(20);

        return view('admin.partners.index')->with([
            'partners'=>$partners,
            'request'=>$request,

        ]);
    }


    public function PartnerStatusChange(Request $request){


        $partner=Partner::find($request->id);
        if($partner){
            $partner->status=$request->status;
            $partner->save();
            return response()->json(['status'=>$request->status]);
        }
    }


    public function ViewPartnerDetail($id){

        $partner=Partner::find($id);
        if($partner){
            $languages= DB::table('languages')->get();
            return view('admin.partners.partner-detail',compact('partner','languages'));
        }
    }

    public function PartnerMultiplierSettingSave(Request  $request){

        $partner=Partner::find($request->partner_id);
        if($partner){
            $partner->price_multiplier=$request->price_multiplier;
            $partner->compare_at_price_multiplier=$request->compare_at_price_multiplier;
            $partner->save();
            return back()->with('success', 'Partner Multiplier Setting Saved Successfully');
        }
    }


    public function PartnerSettingSave(Request $request){

        $partner=Partner::find($request->partner_id);
        if($partner){
            $partner->name=$request->name;
            $partner->shopify_token=$request->shopify_token;
            $partner->api_key=$request->api_key;
            $partner->api_secret=$request->api_key;
            $partner->store_language_id=$request->store_language;
            $partner->save();
            return back()->with('success', 'Partner Setting Saved Successfully');
        }
    }



    public function SyncProduct($id){
        $partner=Partner::find($id);
        if($partner){
            PartnerProductsSyncJob::dispatch($partner->id);
        }
        return back()->with('success', 'Partner Products Sync In-Progress');
    }

    public function SyncPartnerProducts($id,$next = null){

        $partner = Partner::find($id);

        $log = new Log();
        $currentTime = now();
        $log->name = 'Sync Products (' . $partner->name . ')';
        $log->date = $currentTime->format('F j, Y');
        $log->start_time = $currentTime->toTimeString();
        $log->status = 'Pending';
        $log->save();

        try {
            $currentTime = now();
            $log->end_time = $currentTime->toTimeString();
            $log->status = 'In-Progress';
            $log->save();
            $products = $this->api($partner)->rest('get', '/admin/products.json', [
                'limit' => 250,
            ]);
            $products = json_decode(json_encode($products));

            foreach ($products->body->products as $index => $product) {

                $productController = new ProductController();
                $productController->createShopifySupplierProducts($product, $id);
            }
            if (isset($products->link->next)) {
                $this->SyncPartnerProducts($id, $products->link->next);
            }

            $currentTime = now();
            $log->date = $currentTime->format('F j, Y');
            $log->end_time = $currentTime->toTimeString();
            $log->status = 'Complete';
            $log->save();
            return back()->with('success', 'Partner Products Sync Successfully');
        }catch (\Exception $exception){

            $currentTime = now();
            $log->end_time = $currentTime->toTimeString();
            $log->status = 'Failed';
            $log->save();

        }
    }


    public function PartnerAutoPushSettingSave(Request $request){
        $partner=Partner::find($request->partner_id);
        if($partner) {
            $partner->autopush_products = isset($request->autopush_products) ? $request->autopush_products : 0;
            $partner->autopush_orders = isset($request->autopush_orders) ? $request->autopush_orders : 0;
            $partner->save();
            return back()->with('success', 'Partner Auto-Push Settings Save Successfully');
        }
    }



}
