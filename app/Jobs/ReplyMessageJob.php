<?php

namespace App\Jobs;

use App\Models\AutomatedMessage;
use App\Models\Customer;
use App\Models\CustomLogs;
use App\Models\CustomMessage;
use App\Models\CustomProducts;
use App\Models\DraftLineItem;
use App\Models\Message;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReplyMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $From;
    public $Body;
    public $To;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($From,$Body,$To)
    {
        $this->From=$From;
        $this->Body=$Body;
        $this->To=$To;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $log=new CustomLogs();
        $log->logs=$this->From;
        $log->save();

        $log=new CustomLogs();
        $log->logs=$this->Body;
        $log->save();

        $log=new CustomLogs();
        $log->logs=$this->To;
        $log->save();
        $customers=Customer::where('phone',$this->From)->first();
        $check_admin_message=Message::where('is_sender','admin')->where('to',$this->From)->latest()->first();
        if($check_admin_message==null){
            $admin_message_id='';
        }
        else{
            $admin_message_id=$check_admin_message->id;
        }


        $shop=User::where('id',$customers->shop_id)->first();

        if($customers==null){
            $customer_id=null;
        }
        else{
            $customer_id=$customers->id;
        }


        $check_latest=Message::where('to',$this->From)->where('is_sender','admin')->latest()->first();
        $custom_messages=CustomMessage::pluck('message')->toArray();

        $custom_messages=implode('|',$custom_messages);

        $custom_messages= preg_replace( "/\r|\n/", "", $custom_messages );
        $custom_messages = str_replace([" ","  ","   "], '', $custom_messages);

        $custom_body_message=preg_replace( "/\r|\n/", "", $check_latest->body );
        $custom_body_message = str_replace([" ","  ","   "], '', $custom_body_message);

        if (str_contains($custom_messages,$custom_body_message)) {

            if(str_contains($this->Body,'B') || str_contains($this->Body,'C') ) {


                $var1 = str_replace("B", "#B", $this->Body);
                $body_request = str_replace("C", "#C", $var1);

                $body_messages = explode('#', $body_request);

                $line_item = array();

                foreach ($body_messages as $body_message) {

                    if (!empty($body_message)) {
                        $message_modify = explode(' ', $body_message);

                        $checking = CustomProducts::where('custom_message_id', $check_latest->custom_message_id)->get();

                        foreach ($checking as $getcheck) {

                            if ($getcheck->products_code == $message_modify[0]) {

                                if(isset($message_modify[1])) {
                                    $quantity = preg_replace("/\r|\n/", "", $message_modify[1]);
                                }
                                else{
                                    goto Message;
                                }

                                if($quantity < 100) {

                                    $product_data = Product::where('id', $getcheck->product_id)->first();

                                    $product_variant = ProductVariant::where('shopify_product_id', $product_data->shopify_id)->latest()->first();


                                    array_push($line_item, [
                                        'name' => $product_data->title,
                                        'title' => $product_data->title,
                                        'price' => $product_variant->price,
                                        'product_id' => $product_data->shopify_id,
                                        'quantity' => $quantity

                                    ]);

                                }
                                else{
                                    $label_message='The limit of order exceed.You can place maximum 99 quantity';
                                    goto Sms;
                                }
                            }
                            else{
                                $check=new Message();
//                                $check->message=json_encode($request->all());
                                $check->to=$this->To;
                                $check->from=$this->From;
                                $check->discard_message=$this->Body;
                                $check->customer_id=$customer_id;
                                $check->is_sender='customer';
                                $check->admin_message_id=$admin_message_id;
                                $check->save();
                                $automated_message = AutomatedMessage::where('type', 'Discard')->first();
                                $label_message=$automated_message->message;
                            }
                        }

                    }
                }


                if(isset($quantity) && $quantity < 100 ) {

                    $order = $shop->api()->rest('POST', '/admin/orders.json', [
                        "order" => [
                            "email" => $customers->email,
                            "financial_status" => "pending",
                            "tags" => "SMS Order",
                            "line_items" => $line_item,
                            "customer" => [
                                "id" => $customers->shopify_id
                            ],
                            "shipping_address" => [
                                "first_name" => $customers->first_name,
                                "last_name" => $customers->last_name,
                                "address1" => $customers->address,
                                "address2" => (isset($customers->address2) ? $customers->address2 : ""),
                                "phone" => $customers->phone,
                                "city" => $customers->city,
                                "province" => $customers->province,
                                "country" => $customers->country,
                                "zip" => $customers->zip
                            ],
                        ]
                    ]);

                }
                else{
                    $label_message='The limit of order exceed.You can place maximum 99 quantity';
                }

                if(isset($order) && $order['errors']==false){


                    $draft_order_id=$order['body']['container']['order']['id'];


                    $users=User::where('id',$shop->id)->first();
                    $users->draft_order_count=$users->draft_order_count+1;
                    $users->save();

                    $check=new Message();
//                    $check->message=json_encode($request->all());
                    $check->to=$this->To;
                    $check->from=$this->From;
                    $check->body=$this->Body;
                    $check->customer_id=$customer_id;
                    $check->is_sender='customer';
                    $check->admin_message_id=$admin_message_id;
                    $check->draft_order_id=$draft_order_id;
                    $check->save();
                    $draft_line_items=$order['body']['container']['order']['line_items'];
                    foreach ($draft_line_items as $draft_line_item ){
                        $draft_order_lineItem=new DraftLineItem();
                        $draft_order_lineItem->draft_shopify_id=$draft_order_id;
                        $draft_order_lineItem->line_item_shopify_id=$draft_line_item['id'];
                        $draft_order_lineItem->title=$draft_line_item['title'];
                        $draft_order_lineItem->email=$order['body']['container']['order']['email'];
                        $draft_order_lineItem->quantity=$draft_line_item['quantity'];
                        $draft_order_lineItem->price=$draft_line_item['price'];
                        $draft_order_lineItem->message_id=$check->id;
                        $draft_order_lineItem->save();
                    }
                    $automated_message = AutomatedMessage::where('type', 'Success')->first();

                    $automated_message=$automated_message->message;
                    $items='';

                    foreach ($line_item as  $get_line_item) {

                        $items=$items."Product: ".$get_line_item['title'].' of Quantity: '.$get_line_item['quantity'].',';

                    }
                    $items=rtrim($items,',');
                    $label_message= str_replace('#{{list-item}}',$items,$automated_message);

                }
            }

            else{
                Message:
                $check=new Message();
//                $check->message=json_encode($request->all());
                $check->to=$this->To;
                $check->from=$this->From;
                $check->discard_message=$this->Body;
                $check->customer_id=$customer_id;
                $check->is_sender='customer';
                $check->admin_message_id=$admin_message_id;
                $check->save();
                $automated_message = AutomatedMessage::where('type', 'Discard')->first();
                $label_message=$automated_message->message;

            }

            $settings = Setting::where('shop_id',$shop->id)->first();
            try {
                Sms:
                $account_sid = $settings->account_sid;
                $auth_token = $settings->auth_token;
                $twilio_number = $settings->twilio_phone_number;

                $client = new \Twilio\Rest\Client($account_sid, $auth_token);
                $client->messages->create($this->From, [
                    'from' => $twilio_number,
                    'body' => $label_message]);

            }

            catch (\Exception $e) {
                dd ($e->getMessage());

            }

        }
    }
}
