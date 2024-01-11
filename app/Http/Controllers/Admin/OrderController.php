<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Jobs\AutoPushOrderJob;
use App\Jobs\PushOrderJob;
use App\Models\CustomLog;
use App\Models\Lineitem;
use App\Models\Order;
use App\Models\Partner;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends BaseController
{
    public function Orders(Request $request){
        $shop=Auth::user();
        $currency=$shop->money_format;
        $currency = str_replace('{{amount}}', '', $currency);
        $orders=Order::query();
        if ($request->search != "") {
            $orders->where('order_number', 'LIKE', '%' . $request->search . '%');
        }
        if($request->status!=""){
            $orders->where('is_pushed',$request->status);
        }
        if($request->date != "" && $request->date!='undefined'){
            $request->date = str_replace('/', '-', $request->date);
            $orders->whereDate('created_at' , date('Y-m-d',strtotime($request->date)));
        }
        $orders=$orders->orderBy('id','desc')->paginate(20)->appends($request->all());
        return view('admin.orders.index',compact('orders','currency','request'));
    }

    public function singleOrder($order, $shop)
    {
        $log=new CustomLog();
        $log->logs=json_encode($order);
        $log->save();

        $setting=Setting::where('shop_id',$shop->id)->first();

        if($order->financial_status!='refunded' && $order->cancelled_at==null  ) {

            $newOrder = Order::where('shopify_order_id', $order->id)->where('shop_id', $shop->id)->first();
            if ($newOrder == null) {
                $newOrder = new Order();
            }
            $newOrder->shopify_order_id = $order->id;
            $newOrder->email = $order->email;
            $newOrder->order_number = $order->name;
            if (isset($order->shipping_address)) {
                $newOrder->shipping_name = $order->shipping_address->name;
                $newOrder->address1 = $order->shipping_address->address1;
                $newOrder->address2 = $order->shipping_address->address2;
                $newOrder->phone = $order->shipping_address->phone;
                $newOrder->city = $order->shipping_address->city;
                $newOrder->zip = $order->shipping_address->zip;
                $newOrder->province = $order->shipping_address->province;
                $newOrder->country = $order->shipping_address->country;
            }
            $newOrder->financial_status = $order->financial_status;
            $newOrder->fulfillment_status = $order->fulfillment_status;
            if (isset($order->customer)) {
                $newOrder->first_name = $order->customer->first_name;
                $newOrder->last_name = $order->customer->last_name;
                $newOrder->customer_phone = $order->customer->phone;
                $newOrder->customer_email = $order->customer->email;
                $newOrder->customer_id = $order->customer->id;
            }
            $newOrder->shopify_created_at = date_create($order->created_at)->format('Y-m-d h:i:s');
            $newOrder->shopify_updated_at = date_create($order->updated_at)->format('Y-m-d h:i:s');
            $newOrder->tags = $order->tags;
            $newOrder->note = $order->note;
            $newOrder->total_price = $order->total_price;
            $newOrder->currency = $order->currency;

            $newOrder->subtotal_price = $order->subtotal_price;
            $newOrder->total_weight = $order->total_weight;
            $newOrder->taxes_included = $order->taxes_included;
            $newOrder->total_tax = $order->total_tax;
            $newOrder->currency = $order->currency;
            $newOrder->total_discounts = $order->total_discounts;
            $newOrder->shop_id = $shop->id;
            $newOrder->save();



            foreach ($order->line_items as $item) {

                $new_line = Lineitem::where('lineitem_id', $item->id)->where('order_id', $newOrder->id)->where('shop_id', $shop->id)->first();
                if ($new_line == null) {
                    $new_line = new Lineitem();
                }

                $product_variant=ProductVariant::where('shopify_product_id',$item->product_id)->where('shopify_id',$item->variant_id)->latest()->first();
                $partner_id=null;
                if($product_variant){
                    $partner_id=$product_variant->partner_id;
                }

                $new_line->shopify_product_id = $item->product_id;
                $new_line->shopify_variant_id = $item->variant_id;
                $new_line->lineitem_id=$item->id;
                $new_line->title = $item->title;
                $new_line->quantity = $item->quantity;
                $new_line->sku = $item->sku;
                $new_line->variant_title = $item->variant_title;
                $new_line->title = $item->title;
                $new_line->vendor = $item->vendor;
                $new_line->price = $item->price;
                $new_line->requires_shipping = $item->requires_shipping;
                $new_line->taxable = $item->taxable;
                $new_line->name = $item->name;
                $new_line->properties = json_encode($item->properties, true);
                $new_line->fulfillable_quantity = $item->fulfillable_quantity;
                $new_line->fulfillment_status = $item->fulfillment_status;
                $new_line->order_id = $newOrder->id;
                $new_line->shop_id = $shop->id;
                $new_line->shopify_order_id = $order->id;
                $new_line->partner_id = $partner_id;
                $new_line->save();
            }

            if($newOrder->is_pushed==0){
                AutoPushOrderJob::dispatch($newOrder->id);
            }
        }
    }

    public function OrderView($id){
        $shop=Auth::user();
        $currency=$shop->money_format;
        $currency = str_replace('{{amount}}', '', $currency);
        $order = Order::where('id',$id)->first();
        $line_items_count=Lineitem::where('order_id',$id)->where('shop_id',$shop->id)->count();
        $line_items = Lineitem::where('order_id', $id)
            ->where('shop_id', $shop->id)
            ->get()
            ->groupBy('partner_id');

        return view('admin.orders.order-detail')->with([
            'order'=>$order,
            'line_items_count'=>$line_items_count,
            'currency'=>$currency,
            'line_items'=>$line_items

        ]);
    }

    public function PushSelectedLineitems(Request $request){
        $shop=Auth::user();
        $order=Order::find($request->order_id);
        if($request->partner_ids){
            $partner_ids=explode(',',$request->partner_ids);
            try {
                foreach ($partner_ids as $partner_id){

                    $partner=Partner::find($partner_id);

                    $line_items=Lineitem::where('partner_id',$partner_id)->where('order_id',$order->id)->get();
                    $line_item_array = array();
                    $line_item_ids = array();
                    $total_weight=0;
                    foreach ($line_items as $line_item){
                        array_push($line_item_ids,$line_item->id);

                        $product_variant=ProductVariant::where('shopify_id',$line_item->shopify_variant_id)->first();
                        array_push($line_item_array, [
                            "variant_id" => $product_variant->partner_shopify_id,
                            'name' => $line_item->title,
                            'title' => $line_item->title,
                            'price' => $product_variant->price,
                            'product_id' => $product_variant->partner_shopify_product_id,
                            'quantity' => $line_item->quantity,
                            "grams"=>$product_variant->grams

                        ]);
                        $total_weight +=$product_variant->grams*$line_item->quantity;
                    }



                    $result = $this->api($partner)->rest('POST', '/admin/orders.json', [
                        "order" => [
                            "email" => $order->email,
                            "financial_status" => "pending",
//                        "tags" => (isset($order->tags) !="" ?$order->tags : null),
                            "line_items" => $line_item_array,
                            'total_weight' => $total_weight,
                            "note"=>$order->note,
                            "shipping_address" => [
                                "first_name" => $order->first_name,
                                "last_name" => $order->last_name,
                                "address1" => $order->address,
                                "address2" => (isset($order->address2) ? $order->address2 : ""),
                                "phone" => $order->phone,
                                "city" => $order->city,
                                "province" => $order->state,
                                "country" => $order->country,
                                "zip" => $order->zip
                            ],
                        ]

                    ]);

                    $result = json_decode(json_encode($result));
                    if($result->errors==false) {
                        Lineitem::whereIn('id', $line_item_ids)->update(['is_pushed' => 1]);
                    }

                }

                $check_lineitems=Lineitem::where('order_id',$request->order_id)->count();
                $check_is_pushed=Lineitem::where('order_id',$request->order_id)->where('is_pushed',1)->count();

                if($check_lineitems==$check_is_pushed){
                    $order->is_pushed=1;
                    $order->save();
                }

                return back()->with('success','Order Pushed Successfully');
            }catch (\Exception $exception){
                return back()->with('error','Server Error');
            }

        }
    }


    public function PushAllLineitems($id){

        $shop=Auth::user();
        $order=Order::find($id);
        $partner_ids = Lineitem::where('order_id', $order->id)
            ->where('is_pushed', 0)
            ->pluck('partner_id')
            ->unique()
            ->toArray();
        try {
            foreach ($partner_ids as $partner_id){

                $partner=Partner::find($partner_id);

                $line_items=Lineitem::where('partner_id',$partner_id)->where('order_id',$order->id)->get();
                $line_item_array = array();
                $line_item_ids = array();
                $total_weight=0;
                foreach ($line_items as $line_item){
                    array_push($line_item_ids,$line_item->id);

                    $product_variant=ProductVariant::where('shopify_id',$line_item->shopify_variant_id)->first();
                    array_push($line_item_array, [
                        "variant_id" => $product_variant->partner_shopify_id,
                        'name' => $line_item->title,
                        'title' => $line_item->title,
                        'price' => $product_variant->price,
                        'product_id' => $product_variant->partner_shopify_product_id,
                        'quantity' => $line_item->quantity,
                        "grams"=>$product_variant->grams

                    ]);
                    $total_weight +=$product_variant->grams*$line_item->quantity;
                }



                $result = $this->api($partner)->rest('POST', '/admin/orders.json', [
                    "order" => [
                        "email" => $order->email,
                        "financial_status" => "pending",
//                        "tags" => (isset($order->tags) !="" ?$order->tags : null),
                        "line_items" => $line_item_array,
                        'total_weight' => $total_weight,
                        "note"=>$order->note,
                        "shipping_address" => [
                            "first_name" => $order->first_name,
                            "last_name" => $order->last_name,
                            "address1" => $order->address,
                            "address2" => (isset($order->address2) ? $order->address2 : ""),
                            "phone" => $order->phone,
                            "city" => $order->city,
                            "province" => $order->state,
                            "country" => $order->country,
                            "zip" => $order->zip
                        ],
                    ]

                ]);

                $result = json_decode(json_encode($result));
                if($result->errors==false) {
                    Lineitem::whereIn('id', $line_item_ids)->update(['is_pushed' => 1]);

                }

            }
            $order->is_pushed=1;
            $order->save();
            return back()->with('success','Order Pushed Successfully');
        }catch (\Exception $exception){
            return back()->with('error','Server Error');
        }

    }

    public function PushSelectedOrders(Request $request){

        if($request->order_ids){
            PushOrderJob::dispatch($request->order_ids);
            return back()->with('success','Order Push is In-Progress');
        }

    }


    public function PushAllOrders(){

        $orders=Order::where('is_pushed',0)->pluck('id')->toArray();
        if(count($orders) > 0) {
            $order_ids = implode(',', $orders);
            PushOrderJob::dispatch($order_ids);
            return back()->with('success', 'Order Push is In-Progress');
        }else{
            return back()->with('error', 'Orders Already Pushed');
        }

    }

}
