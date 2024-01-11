<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Jobs\ApprovedProductJob;
use App\Jobs\DenyProductJob;
use App\Jobs\UpdateApprovedProductJob;
use App\Models\CustomLog;
use App\Models\Log;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stichoza\GoogleTranslate\GoogleTranslate;

class ProductController extends BaseController
{


    public function Products(Request $request){
        $products=Product::query();
        $partners=Partner::all();
        if ($request->search != "") {
            $products->where('title', 'LIKE', '%' . $request->search . '%');
        }
        if($request->partner != ""){
            $products->where('partner_id' , $request->partner);
        }
        if($request->status!=""){
            $products->where('app_status',$request->status);
        }
        if($request->shopify_status!=""){
            $products->where('shopify_status',$request->shopify_status);
        }

        if($request->date != "" && $request->date!='undefined'){
            $request->date = str_replace('/', '-', $request->date);
            $products->whereDate('created_at' , date('Y-m-d',strtotime($request->date)));
        }

        $products=$products->orderBy('updated_at', 'DESC')->paginate(20)->appends($request->all());

        return view('admin.products.index',compact('products','partners','request'));

    }


    public function WebhookProductCreateUpdate(Request $request){


        $product = json_decode(json_encode($request->all()));
        $partner=Partner::where('shop_name',$request->header('x-shopify-shop-domain'))->first();

        $custom=new CustomLog();
        $custom->data='Create shopify webhook';
        $custom->logs=json_encode($product);
        $custom->save();

        $custom=new Customlog();
        $custom->data='shopify header';
        $custom->logs=$request->header('x-shopify-shop-domain');
        $custom->save();



        $this->createShopifySupplierProducts($product, $partner->id);

        return true;
    }

    public function WebhookProductDelete(Request $request){
        $product = json_decode(json_encode($request->all()));
        $partner=Partner::where('shop_name',$request->header('x-shopify-shop-domain'))->first();
        $dellproduct = Product::where('partner_shopify_id',$product->id)->where('partner_id',$partner->id)->first();
        $product_id = $product->id;
        $productvarients = ProductVariant::where('partner_shopify_product_id',$product_id)->get();
        foreach ($productvarients as $varient){
            $varient->delete();
        }
        $dellproduct->delete();
        return true;
    }


    public function createShopifySupplierProducts($product, $id)
    {

        $shop=User::where('name',env('SHOP_NAME'))->first();
        $p = Product::where('partner_shopify_id', $product->id)->where('partner_id',$id)->first();
        $partner=Partner::find($id);
        if($shop && $shop->language_id){
            $language=DB::table('languages')->where('id',$shop->language_id)->first();
            if($language){
                $shop_language_code=$language->code;
            }
        }
        if($partner && $partner->store_language_id){
            $p_language=DB::table('languages')->where('id',$partner->store_language_id)->first();
            if($p_language){
                $partner_language_code=$p_language->code;
            }
        }
        $tr = new GoogleTranslate($shop_language_code, $partner_language_code);
        if($partner->store_language_id==$shop->language_id){

            $p_title=$product->title;
            $p_description=$product->body_html;
            $p_type=$product->product_type;

        }else{
            $p_title=null;
            $p_description=null;
            $p_type=null;
            if($product->title){
            $p_title=$tr->translate($product->title);
            }
            if($product->body_html) {
                $p_description = $tr->translate($product->body_html);
            }
            if($product->product_type) {
                $p_type = $tr->translate($product->product_type);
            }

        }
        if ($p === null) {
            $p = new Product();
            $p->title = $p_title;
            $p->description =$p_description;
            $p->handle = $product->handle;
            $p->vendor = $product->vendor;
            $p->type = $p_type;
            $p->tags = $product->tags;
            $p->options = json_encode($product->options);
            $p->status = $product->status;
            $p->published_at = $product->published_at;

            $log=new Log();
            $currentTime = now();
            $log->name='Create Product ('.$partner->name.')';
            $log->date = $currentTime->format('F j, Y');
            $log->start_time = $currentTime->toTimeString();
            $log->end_time = $currentTime->toTimeString();
            $log->status = 'Complete';
            $log->save();
        }else{
            $log=new Log();
            $currentTime = now();
            $log->name='Update Product ('.$partner->name.')';
            $log->date = $currentTime->format('F j, Y');
            $log->start_time = $currentTime->toTimeString();
            $log->end_time = $currentTime->toTimeString();
            $log->status = 'Complete';
            $log->save();
        }
        if ($product->images) {
            $image = $product->images[0]->src;
        } else {
            $image = '';
        }

            $p->partner_shopify_id = $product->id;
            $p->partner_id = $id;
        $p->featured_image = $image;
            $p->save();

        if (count($product->variants) >= 1) {
            foreach ($product->variants as $variant) {
                $v = ProductVariant::where('partner_shopify_id', $variant->id)->where('partner_id',$id)->first();
                if ($v === null) {
                    $v = new ProductVariant();
                }
                $v->partner_shopify_id = $variant->id;
                $v->partner_shopify_product_id = $variant->product_id;
                $v->partner_id = $id;
                $v->title = $variant->title;
                $v->option1 = $variant->option1;
                $v->option2 = $variant->option2;
                $v->option3 = $variant->option2;
                $v->sku = $variant->sku;
                $v->requires_shipping = $variant->requires_shipping;
                $v->fulfillment_service = $variant->fulfillment_service;
                $v->taxable = $variant->taxable;
                if (isset($product->images)){
                    foreach ($product->images as $image){
                        if (isset($variant->image_id)){
                            if ($image->id == $variant->image_id){
                                $v->image = $image->src;
                            }
                        }else{
                            $v->image = "";
                        }
                    }
                }
                $v->price = $variant->price;
                $v->compare_at_price = $variant->compare_at_price;
                $v->weight = $variant->weight;
                $v->grams = $variant->grams;
                $v->weight_unit = $variant->weight_unit;
                $v->partner_inventory_item_id = $variant->inventory_item_id;
                $v->stock = $variant->inventory_quantity;
                $v->inventory_management = $variant->inventory_management;
                $v->inventory_policy = $variant->inventory_policy;
                $v->barcode = $variant->barcode;
                $v->image_id = $variant->image_id;
                $v->save();
            }
        }


        if(count($product->images) > 0){

            foreach ($product->images as $image) {

                $imgCheck=ProductImage::where('image_id',$image->id)->exists();
                if (!$imgCheck)
                {
                    $product_img = new ProductImage();
                    $product_img->image = $image->src;
                    $product_img->alt = $image->alt;
                    $product_img->image_id = $image->id;
                    $product_img->product_id = $product->id;
                    $product_img->partner_id = $id;
                    $product_img->save();
                }
            }
        }


        UpdateApprovedProductJob::dispatch($p->id);

    }


    public function CreateProductShopify($id)
    {
        $shop=Auth::user();
        $product = Product::find($id);
        $setting=Setting::where('shop_id',$shop->id)->first();


        $partner = Partner::find($product->partner_id);

        if($partner && $partner->price_multiplier){
            $price_multiplier=$partner->price_multiplier;

        }else{
            $price_multiplier=$setting->base_price_multiplier;

        }
        if($partner && $partner->compare_at_price_multiplier){

            $compare_at_price_multiplier=$partner->compare_at_price_multiplier;
        }else{
            $compare_at_price_multiplier=$setting->base_compare_at_price_multiplier;
        }




        if($product->app_status==0 || $product->app_status==3)
        {
            $product->shopify_status='In-Progress';
            $product->save();

            $variants=[];
            $product_variants =ProductVariant::where('partner_shopify_product_id',$product->partner_shopify_id)->where('partner_id',$partner->id)->get();
            $variant_image_ids_array = array();
            foreach($product_variants as $index=> $product_variant)
            {
                array_push($variant_image_ids_array,$product_variant->image_id);
                if($product_variant->inventory_policy){
                    $inventory_policy=$product_variant->inventory_policy;
                }else{
                    $inventory_policy='continue';
                }
                $variants[]=array(
                    "title" => $product_variant->title,
                    "option1" => $product_variant->option1,
                    "option2" => $product_variant->option2,
                    "option3" => $product_variant->option3,
                    "sku"     => $product_variant->sku,
                    "price"   => $product_variant->price*$price_multiplier,
                    "compare_at_price" =>$product_variant->compare_at_price*$compare_at_price_multiplier,
                    "grams"   => $product_variant->grams,
                    "taxable" => $product_variant->taxable,
                    "inventory_management" => $product_variant->inventory_management,
                    "inventory_policy" => $inventory_policy,
                    "barcode" => $product_variant->barcode,
                    "inventory_quantity" => $product_variant->stock
                );
            }

            $products_array = array(
                "product" => array(
                    "title"        => $product->title,
                    "body_html"    => $product->description,
                    "vendor"       =>  $product->vendor,
                    "status"=>$product->status,
                    "product_type" => $product->type,
                    "published"    => true ,
                    "tags"         => explode(",",$product->tags),
                    "variants"     =>$variants,
                    "options"     =>  json_decode($product->options),

                )
            );


            $result = $this->api()->rest('post', '/admin/products.json',$products_array);
            $result = json_decode(json_encode($result));
            if($result->errors==false) {

                $shopify_product_id = $result->body->product->id;

                Product::where('id', $product->id)->update(['shopify_id' => $shopify_product_id, 'app_status' => '1', 'approve_date' => Carbon::now()]);

                foreach ($result->body->product->variants as $prd) {
                    ProductVariant::where('partner_shopify_product_id',$product->partner_shopify_id)->where('sku', $prd->sku)->update(['inventory_item_id' => $prd->inventory_item_id, 'shopify_id' => $prd->id, 'shopify_product_id' => $shopify_product_id]);
                }

                $this->shopifyUploadImage($shopify_product_id, $product->partner_shopify_id, $variant_image_ids_array);


                $product->shopify_status = 'Complete';
                $product->save();
            }else{
                $product->shopify_status='Failed';
                $product->save();
            }
        }

//        else if($product->status==2)
//        {
//            $setting=Setting::first();
//            if($setting){
//                $API_KEY =$setting->api_key;
//                $PASSWORD = $setting->password;
//                $SHOP_URL =$setting->shop_url;
//
//            }else{
//                $API_KEY = '6bf56fc7a35e4dc3879b8a6b0ff3be8e';
//                $PASSWORD = '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';
//                $SHOP_URL = 'cityshop-company-store.myshopify.com';
//            }
//
//            $shopify_id=$product->shopify_id;
//            $SHOPIFY_API_meta = "https://$API_KEY:$PASSWORD@$SHOP_URL/admin/api/2022-10/products/$shopify_id/metafields.json";
//
//            $curl = curl_init();
//            curl_setopt($curl, CURLOPT_URL, $SHOPIFY_API_meta);
//            $headers = array(
//                "Authorization: Basic " . base64_encode("$API_KEY:$PASSWORD"),
//                "Content-Type: application/json",
//                "charset: utf-8"
//            );
//            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($curl, CURLOPT_VERBOSE, 0);
//            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
//            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
////            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
//            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//            $response = curl_exec($curl);
//            curl_close($curl);
//            $res = json_decode($response, true);
//
//            if(isset($res['metafields'])) {
//                foreach ($res['metafields'] as $ress) {
//
//                    if ($ress['key'] =='key_ingredients') {
//                        $SHOPIFY_update = "https://$API_KEY:$PASSWORD@$SHOP_URL/admin/api/2022-10/metafields/" . $ress['id'] . ".json";
//                        $curl = curl_init();
//                        curl_setopt($curl, CURLOPT_URL, $SHOPIFY_update);
//                        $headers = array(
//                            "Authorization: Basic " . base64_encode("$API_KEY:$PASSWORD"),
//                            "Content-Type: application/json",
//                            "charset: utf-8"
//                        );
//                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//                        curl_setopt($curl, CURLOPT_VERBOSE, 0);
//                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
//                        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
////            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($metafield_data));
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//                        $response = curl_exec($curl);
//
//                        curl_close($curl);
//                    }
//                    if ($ress['key'] =='how_to_use') {
//                        $SHOPIFY_update = "https://$API_KEY:$PASSWORD@$SHOP_URL/admin/api/2022-10/metafields/" . $ress['id'] . ".json";
//                        $curl = curl_init();
//                        curl_setopt($curl, CURLOPT_URL, $SHOPIFY_update);
//                        $headers = array(
//                            "Authorization: Basic " . base64_encode("$API_KEY:$PASSWORD"),
//                            "Content-Type: application/json",
//                            "charset: utf-8"
//                        );
//                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//                        curl_setopt($curl, CURLOPT_VERBOSE, 0);
//                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
//                        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
////            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($metafield_data));
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//                        $response = curl_exec($curl);
//
//                        curl_close($curl);
//                    }
//                    if ($ress['key'] =='who_can_use') {
//                        $SHOPIFY_update = "https://$API_KEY:$PASSWORD@$SHOP_URL/admin/api/2022-10/metafields/" . $ress['id'] . ".json";
//                        $curl = curl_init();
//                        curl_setopt($curl, CURLOPT_URL, $SHOPIFY_update);
//                        $headers = array(
//                            "Authorization: Basic " . base64_encode("$API_KEY:$PASSWORD"),
//                            "Content-Type: application/json",
//                            "charset: utf-8"
//                        );
//                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//                        curl_setopt($curl, CURLOPT_VERBOSE, 0);
//                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
//                        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
////            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($metafield_data));
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//                        $response = curl_exec($curl);
//
//                        curl_close($curl);
//                    }
//                    if ($ress['key'] =='why_mama_earth') {
//                        $SHOPIFY_update = "https://$API_KEY:$PASSWORD@$SHOP_URL/admin/api/2022-10/metafields/" . $ress['id'] . ".json";
//                        $curl = curl_init();
//                        curl_setopt($curl, CURLOPT_URL, $SHOPIFY_update);
//                        $headers = array(
//                            "Authorization: Basic " . base64_encode("$API_KEY:$PASSWORD"),
//                            "Content-Type: application/json",
//                            "charset: utf-8"
//                        );
//                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//                        curl_setopt($curl, CURLOPT_VERBOSE, 0);
//                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
//                        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
////            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($metafield_data));
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//                        $response = curl_exec($curl);
//
//                        curl_close($curl);
//                    }
//                    if ($ress['key'] =='different_shades') {
//                        $SHOPIFY_update = "https://$API_KEY:$PASSWORD@$SHOP_URL/admin/api/2022-10/metafields/" . $ress['id'] . ".json";
//                        $curl = curl_init();
//                        curl_setopt($curl, CURLOPT_URL, $SHOPIFY_update);
//                        $headers = array(
//                            "Authorization: Basic " . base64_encode("$API_KEY:$PASSWORD"),
//                            "Content-Type: application/json",
//                            "charset: utf-8"
//                        );
//                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//                        curl_setopt($curl, CURLOPT_VERBOSE, 0);
//                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
//                        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
////            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($metafield_data));
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//                        $response = curl_exec($curl);
//
//                        curl_close($curl);
//                    }
//                    if ($ress['key'] =='faqs') {
//                        $SHOPIFY_update = "https://$API_KEY:$PASSWORD@$SHOP_URL/admin/api/2022-10/metafields/" . $ress['id'] . ".json";
//                        $curl = curl_init();
//                        curl_setopt($curl, CURLOPT_URL, $SHOPIFY_update);
//                        $headers = array(
//                            "Authorization: Basic " . base64_encode("$API_KEY:$PASSWORD"),
//                            "Content-Type: application/json",
//                            "charset: utf-8"
//                        );
//                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//                        curl_setopt($curl, CURLOPT_VERBOSE, 0);
//                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
//                        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
////            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($metafield_data));
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//                        $response = curl_exec($curl);
//
//                        curl_close($curl);
//                    }
//                    if($ress['namespace']=='variants'){
//                        $SHOPIFY_update = "https://$API_KEY:$PASSWORD@$SHOP_URL/admin/api/2022-10/metafields/" . $ress['id'] . ".json";
//                        $curl = curl_init();
//                        curl_setopt($curl, CURLOPT_URL, $SHOPIFY_update);
//                        $headers = array(
//                            "Authorization: Basic " . base64_encode("$API_KEY:$PASSWORD"),
//                            "Content-Type: application/json",
//                            "charset: utf-8"
//                        );
//                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//                        curl_setopt($curl, CURLOPT_VERBOSE, 0);
//                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
//                        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
////            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($metafield_data));
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//                        $response = curl_exec($curl);
//
//                        curl_close($curl);
//
//                    }
//
//                }
//            }
//
//
//            $options_array=array();
//            $category=Category::find($product->category);
//
//
//            $product_infos =ProductInfo::where('product_id',$product['id'])->get();
//            $groupedData = [];
//            $groupedData1 = [];
//            $values = array();
//            foreach ($product_infos as $index=> $product_info) {
//
//
//                $value = [
//                    "hex_code" => $product_info->hex_code,
//                    "swatch_image" => $product_info->swatch_image,
//                    "volume" => $product_info->volume,
//                    'dimensions' => $product_info->dimensions_text,
//                    'shelf_life' => $product_info->shelf_life,
//                    'temp_require' => $product_info->temp_require,
//                    'height' => $product_info->height,
//                    'width' => $product_info->width,
//                    'length' => $product_info->length,
//                    'sku'=>$product_info->sku
//                ];
//                array_push($values, $value);
//
//                $varientName = $product_info->varient_name;
//                $varientValue = $product_info->varient_value;
//
//
//                $varient1Name = $product_info->varient1_name;
//                $varient1Value = $product_info->varient1_value;
//
//
//                if($varientName!=''|| $varientName!=null){
//                    // Check if the varient_name already exists in the grouped data array
//                    if (array_key_exists($varientName, $groupedData)) {
//                        // If it exists, add the varient_value to the existing array
//                        $groupedData[$varientName]['value'][] = $varientValue;
//                    } else {
//                        // If it doesn't exist, create a new entry with the varient_name and an array containing the varient_value
//                        $groupedData[$varientName] = [
//                            'name' => $varientName,
//                            'value' => [$varientValue]
//                        ];
//                    }
//                }
//
//
//                if($varient1Name!=''|| $varient1Name!=null){
//                    // Check if the varient_name already exists in the grouped data array
//                    if (array_key_exists($varient1Name, $groupedData1)) {
//                        // If it exists, add the varient_value to the existing array
//                        $grouped1Data[$varient1Name]['value'][] = $varient1Value;
//                    } else {
//                        // If it doesn't exist, create a new entry with the varient_name and an array containing the varient_value
//                        $groupedData[$varient1Name] = [
//                            'name' => $varient1Name,
//                            'value' => [$varient1Value]
//                        ];
//                    }
//                }
//
//            }
//            $metafield_variant_data=[
//                "metafield" =>
//                    [
//                        "key" => 'detail',
//                        "value" => json_encode($values),
//                        "type" => "json_string",
//                        "namespace" => "variants",
//
//                    ]
//            ];
//
//
//            $SHOPIFY_API = "https://$API_KEY:$PASSWORD@$SHOP_URL/admin/api/2022-10/products/$shopify_id/metafields.json";
//
//            $curl = curl_init();
//            curl_setopt($curl, CURLOPT_URL, $SHOPIFY_API);
//            $headers = array(
//                "Authorization: Basic ".base64_encode("$API_KEY:$PASSWORD"),
//                "Content-Type: application/json",
//                "charset: utf-8"
//            );
//            curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
//            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($curl, CURLOPT_VERBOSE, 0);
//            //curl_setopt($curl, CURLOPT_HEADER, 1);
//            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
//            //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
//            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($metafield_variant_data));
//            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//
//            $response1 = curl_exec ($curl);
//
//
//            curl_close ($curl);
//
//
//            $result_options = array_values($groupedData);
//            $result1_options = array_values($groupedData1);
//
//            foreach ($result_options as $index=>  $result_option) {
//
//                array_push($options_array, [
//                    'name' => $result_option['name'],
//                    'position' => $index + 1,
//                    'values' => $result_option['value']
//                ]);
//
//            }
//            foreach ($result1_options as $index=>  $result1_option) {
//                array_push($options_array, [
//                    'name' => $result1_option['name'],
//                    'position' => $index + 1,
//                    'values' => $result1_option['value']
//                ]);
//            }
//
//            $tags=$product->tags;
//            if($product->orignal_vendor){
//                $result = strcmp($store->name, $product->orignal_vendor);
//                if ($result != 0) {
//                    $tags = $product->tags . ',' . $product->orignal_vendor;
//                }
//            }
//
//            $use_store_hsncode=0;
//            if($product->product_type_id){
//                $product_type_check=ProductType::find($product->product_type_id);
//                if($product_type_check){
//                    if($product_type_check->hsn_code) {
//                        $use_store_hsncode=1;
//                        $tags = $tags . ',HSN:' . $product_type_check->hsn_code;
//                    }
//                }
//            }
//
//            if($store && $store->hsn_code){
//                if($use_store_hsncode==0){
//                    $tags = $tags . ',HSN:' . $store->hsn_code;
//                }
//            }
//
//            $data['product']=array(
//                "id" => $shopify_id,
//                "title" => $product->title,
//                "tags"   => $tags,
//                "product_type" => $category->category,
//                "options"     =>  $options_array,
//                "metafields"=>$metafield_data
//
//            );
//
//
//
//
//            $SHOPIFY_API = "https://$API_KEY:$PASSWORD@$SHOP_URL/admin/api/2022-10/products/$shopify_id.json";
//            $curl = curl_init();
//            curl_setopt($curl, CURLOPT_URL, $SHOPIFY_API);
//            $headers = array(
//                "Authorization: Basic ".base64_encode("$API_KEY:$PASSWORD"),
//                "Content-Type: application/json",
//                "charset: utf-8"
//            );
//            curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
//            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($curl, CURLOPT_VERBOSE, 0);
//            //curl_setopt($curl, CURLOPT_HEADER, 1);
//            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
//            //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
//            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
//            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//
//            $response = curl_exec ($curl);
//
//            curl_close ($curl);
//            Product::where('id', $product['id'])->update(['edit_status' => 0, 'status' => '1', 'approve_date' => Carbon::now()]);
//
//
//
//            if(count($product_infos) > 0) {
//
//                foreach ($product_infos as $index=> $product_info) {
//                    try {
//
//                        //update stock on live store
//                        $productController = new ProductController();
//                        $productController->updateStockLiveStore($product_info->inventory_id, $product_info->stock, $product_info->inventory_item_id);
//                        //update variant
//                        $productController->updateVarianatLiveStore($product_info->id);
//                        ///create new varient
//                        $invid = $product_info->inventory_id;
//
//
//
//
//                        if ($product_info->varient_name != '' && $product_info->varient_value != '') {
//                            $data['variant'] = array(
//                                "id" => $invid,
//                                "option1" => $product_info->varient_value,
//                                "option2" => $product_info->varient1_value,
//                                "sku" => $product_info->sku,
//                                "price" => $product_info->price_usd,
//                                "compare_at_price" => $product_info->price_usd,
//                                "grams" => $product_info->pricing_weight,
//                                "taxable" => false,
//                                "inventory_management" => ($product_info->stock) ? null : "shopify",
//                            );
//
//
//
//
//                        } else {
//                            $data['variant'] = array(
//                                "id" => $invid,
//                                "sku" => $product_info->sku,
//                                "price" => $product_info->price_usd,
//                                "compare_at_price" => $product_info->price_usd,
//                                "grams" => $product_info->grams,
//                                "taxable" => false,
//                                "inventory_management" => ($product_info->stock) ? null : "shopify",
//                            );
//                        }
//
//
//
//
//
//
////                $API_KEY = '6bf56fc7a35e4dc3879b8a6b0ff3be8e';
////                $PASSWORD = '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';
////                $SHOP_URL = 'cityshop-company-store.myshopify.com';
//                        $SHOPIFY_API = "https://$API_KEY:$PASSWORD@$SHOP_URL/admin/api/2022-10/variants/$invid.json";
//                        $curl = curl_init();
//                        curl_setopt($curl, CURLOPT_URL, $SHOPIFY_API);
//                        $headers = array(
//                            "Authorization: Basic " . base64_encode("$API_KEY:$PASSWORD"),
//                            "Content-Type: application/json",
//                            "charset: utf-8"
//                        );
//                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//                        curl_setopt($curl, CURLOPT_VERBOSE, 0);
//                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
//                        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
//                        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//                        $response = curl_exec($curl);
//                        curl_close($curl);
//                        $res = json_decode($response, true);
//                        //echo "<pre>"; print_r($data); print_r($res); die();
//
//                        ////Update Image for variant
//                        $productDetails = Product::find($product_info->product_id);
//                        if ($productDetails->shopify_id != null && $productDetails->status == 1) {
//                            $shopify_product_id = $productDetails->shopify_id;
//                            $SHOPIFY_API = "https://$API_KEY:$PASSWORD@$SHOP_URL/admin/api/2020-04/products/$shopify_product_id/images.json";
//                            $variant_id = $product_info->id;
//                            $imagesResult = ProductImages::where('variant_ids', $variant_id)->first();
//                            if ($imagesResult) {
//                                $data['image'] = array(
//                                    'src' => $imagesResult->image,
//                                    'variant_ids' => array($product_info->inventory_id),
//                                );
//                                $curl = curl_init();
//                                curl_setopt($curl, CURLOPT_URL, $SHOPIFY_API);
//                                $headers = array(
//                                    "Authorization: Basic " . base64_encode("$API_KEY:$PASSWORD"),
//                                    "Content-Type: application/json",
//                                    "charset: utf-8"
//                                );
//                                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//                                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//                                curl_setopt($curl, CURLOPT_VERBOSE, 0);
//                                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
//                                curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
//                                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
//                                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//                                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//                                $response = curl_exec($curl);
//                                curl_close($curl);
//                                $img_result = json_decode($response, true);
//                                ProductImages::where('id', $imagesResult->id)->update(['image_id' => $img_result['image']['id']]);
//                            }
//                        }
//                        $location_id = Helpers::DiffalultLocation();
//                        ProductInventoryLocation::updateOrCreate(
//                            ['items_id' => $product_info->inventory_item_id, 'location_id' => $location_id],
//                            ['items_id' => $product_info->inventory_item_id, 'stock' => $product_info->stock, 'location_id' => $location_id]
//                        );
//
//
//                    }catch (\Exception $exception){
//                        dd($exception->getMessage());
//                    }
//                }
//            }
//
//
//        }
        return back()->with('success','Product Created Successfully');
    }


    public function RejectProduct($id)
    {
        $product=Product::find($id);
        if($product) {
            $result = $this->api()->rest('delete', '/admin/products/'.$product->shopify_id.'.json');
            if($result['errors']==false){
           Product::where('id', $id)->update(['app_status' => '3','shopify_status'=>'Pending','shopify_id'=>null, 'approve_date' => Carbon::now()]);
            return back()->with('success', 'Product Deleted Successfully');
        }else{
                return back()->with('error', 'Server Error');
            }
        }
    }


    public function ProductView($id){

        $shop=Auth::user();
        $product = Product::where('id',$id)->first();
        $partner=Partner::find($product->partner_id);
        $tags=explode(',',$product->tags);
        $setting=Setting::where('shop_id',$shop->id)->first();

        $product_variants=ProductVariant::where('partner_shopify_product_id',$product->partner_shopify_id)->where('partner_id',$product->partner_id)->get();

        $product_images=ProductImage::where('product_id',$product->partner_shopify_id)->get();
        $product_options=json_decode($product->options);

        if($partner && $partner->price_multiplier){
            $price_multiplier=$partner->price_multiplier;

        }else{
            $price_multiplier=$setting->base_price_multiplier;

        }
        if($partner && $partner->compare_at_price_multiplier){
            $compare_at_price_multiplier=$partner->compare_at_price_multiplier;
        }else{
            $compare_at_price_multiplier=$setting->base_compare_at_price_multiplier;
        }



        $shop=Auth::user();
        return view('admin.products.product-detail')->with([
            'product'=>$product,
            'partner'=>$partner,
            'tags'=>$tags,
            'product_variants'=>$product_variants,
            'product_options'=>$product_options,
            'product_images'=>$product_images,
            'price_multiplier'=>$price_multiplier,
            'compare_at_price_multiplier'=>$compare_at_price_multiplier
        ]);
    }


    public function UpdateVariantDetail(Request $request){

        $product_variant=ProductVariant::find($request->variant_id);
        if($product_variant){
            $product_variant->title=$request->title;
            $product_variant->price=$request->price;
            $product_variant->save();
        }
        return back()->with('success','Variant Detail Updated Successfully');
    }


    public function UpdateProductDetail(Request $request){

        $product=Product::find($request->id);
        if($product){
            $product->title=$request->title;
            $product->description=$request->description;
            $product->tags=$request->tags;
            $product->type=$request->product_type;
            $product->vendor=$request->vendor;
            $product->is_changed=1;
            $product->save();


            if($product->shopify_id) {
                $products_array = array(
                    "product" => array(
                        "title" => $product->title,
                        "body_html" => $product->description,
                        "vendor" => $product->vendor,
                        "product_type" => $product->type,
                        "tags" => explode(",", $product->tags),

                    )
                );


                $result = $this->api()->rest('put', '/admin/products/' . $product->shopify_id . '.json', $products_array);
                $result = json_decode(json_encode($result));
            }
        }
        return back()->with('success','Product Detail Updated Successfully');
    }

    public function shopifyUploadImage($shopify_product_id,$partner_shopify_product_id,$variant_ids_array)
    {


        $product_images = ProductImage::where('product_id',$partner_shopify_product_id)->get();

        foreach($product_images as $index=> $img_val)
        {

            $product_variant=ProductVariant::where('image_id',$img_val->image_id)->first();

            if($product_variant) {

                $data = array(
                    'src' => $img_val->image,
                    'alt' => $img_val->alt,
                    'variant_ids' => [$product_variant->shopify_id]

                );
            }else{
                $data = array(
                    'src' => $img_val->image,
                    'alt' => $img_val->alt,


                );
            }


            $result = $this->api()->rest('post', '/admin/products/'.$shopify_product_id.'/images.json', [
                'image' => $data
            ]);
//            $img_result=json_decode($result, true);

//            if(isset($img_result['image']['id']))
//                ProductImage::where('id', $img_val->id)->update(['image_id' => $img_result['image']['id']]);

        }
    }


    public function UpdateSelectedProducts(Request $request){

        if($request->action=='approve' && $request->product_ids){
        ApprovedProductJob::dispatch($request->product_ids);
            return back()->with('success','Products Approval In-Progress');
        }

        if($request->action=='deny' && $request->product_ids){
            DenyProductJob::dispatch($request->product_ids);
            return back()->with('success','Products Deny In-Progress');
        }

    }

    public function ApproveAll(){

    $products=Product::whereNull('shopify_id')->pluck('id')->toArray();
    $product_ids=implode(',',$products);
        ApprovedProductJob::dispatch($product_ids);
        return back()->with('success','Products Approval In-Progress');

    }

    public function DenyAll(){

        $products=Product::whereNotNull('shopify_id')->pluck('id')->toArray();
        $product_ids=implode(',',$products);
        DenyProductJob::dispatch($product_ids);
        return back()->with('success','Products Deny In-Progress');
    }






}
