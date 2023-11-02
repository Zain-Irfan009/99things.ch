<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Osiset\BasicShopifyAPI\BasicShopifyAPI;
use Osiset\BasicShopifyAPI\Options;
use Osiset\BasicShopifyAPI\Session;

class PartnerController extends Controller
{

    public function PartnerView(){

        $partners=Partner::paginate(20);
        return view('admin.partners.index',compact('partners'));
    }


    public function SavePartner(Request $request){



        $user=new User();
        $user->email=$request->name.'@'.$request->shop_name.'.com';
        $user->password=Hash::make('12345678');
        $user->save();

        $partner=new Partner();
        $partner->name=$request->name;
        $partner->shop_name=$request->shop_name;
        $partner->shopify_domain=$request->shopify_domain;
        $partner->shopify_token=$request->shopify_token;
        $partner->api_key=$request->api_key;
        $partner->api_secret=$request->api_secret;
        $partner->save();

        // Create options for the API
        $options = new Options();
        $options->setType(true); // Makes it private
        $options->setVersion('2022-07');
        $options->setApiKey($partner->api_key);
        $options->setApiPassword($partner->shopify_token);

        // Create the client and session
        $api = new BasicShopifyAPI($options);
        $api->setSession(new Session($partner->shop_name));

        $webhookcreate = [
            "webhook" => [
                "topic"=>"products/create",
                "address"=> "https://phpstack-711164-2493540.cloudwaysapps.com/product-webhook",
                "format" => "json",
            ]
        ];

        $webhookupdate = [
            "webhook" => [
                "topic"=>"products/update",
                "address"=> "https://phpstack-711164-2493540.cloudwaysapps.com/product-webhook",
                "format" => "json",
            ]
        ];

        $webhookdelete = [
            "webhook" => [
                "topic"=>"products/delete",
                "address"=> "https://phpstack-711164-2493540.cloudwaysapps.com/delete-product-webhook",
                "format" => "json",
            ]
        ];


        $response_create = $api->rest('POST', '/admin/api/webhooks.json', $webhookcreate);
        $response_create = json_decode(json_encode($response_create));
        $response_update = $api->rest('POST', '/admin/api/webhooks.json', $webhookupdate);
        $response_update = json_decode(json_encode($response_update));

        $response_delete = $api->rest('POST', '/admin/api/webhooks.json', $webhookdelete);
        $response_delete = json_decode(json_encode($response_delete));

        if($response_create->errors == false && $response_update->errors == false && $response_delete->errors == false){

            $partner->webhook_product_create_id= $response_create->body->webhook->id;
            $partner->webhook_product_update_id= $response_update->body->webhook->id;
            $partner->webhook_product_delete_id= $response_delete->body->webhook->id;
            $partner->save();
        }

        return back()->with('success','Partner Details Save Successfully');
    }
}
