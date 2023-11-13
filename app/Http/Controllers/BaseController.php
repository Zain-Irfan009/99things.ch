<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Osiset\BasicShopifyAPI\BasicShopifyAPI;
use Osiset\BasicShopifyAPI\Options;
use Osiset\BasicShopifyAPI\Session;

class BaseController extends Controller
{
    public function api($partner = null)
    {

        if ($partner == null) {
            $shop = auth()->user()->name;
            $access_token=auth()->user()->password;
        }else{
            $shop=$partner->shop_name;
            $access_token=$partner->shopify_token;
        }
//        dd($request,$shop);
        // Create options for the API
        $options = new Options();
        $options->setVersion('2023-01');

// Create the client and session
        $api = new BasicShopifyAPI($options);
        $api->setSession(new Session($shop, $access_token));

        return $api;
    }
}
