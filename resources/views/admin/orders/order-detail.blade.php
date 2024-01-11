@extends('admin.layout.index')

<style>

</style>
@section('content')



            <div class="row row-cards">
    <div class="col-lg-12 col-md-12" style="margin-top: 0;">
        <div class="">
            <div class="col-md-12 ">
                <div class="row">
                    <div class="col-md-6 d-flex">
                        <div class="custom-left-arrow-div " >
                            <a style="text-decoration: none; padding:19px; font-size: 25px; color: black;" href="{{route('orders')}}"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
                        </div>
                        <div><h2 style="margin-top: 3px;">{{$order->order_number}}</h2></div>
                        <h4 style="margin-top: 6px;border-radius: 8px;"><span class="badge bg-warning mx-2">{{$order->financial_status}}</span></h4>

                        @if($order->fulfillment_status==null)
                            <h4 style="margin-top: 6px;border-radius: 8px;"><span class="badge bg-danger ">Unfulfilled</span></h4>

                        @else
                            <h4 style="margin-top: 4px"><span class="badge bg-warning mx-2">{{$order->fulfillment_status}}</span></h4>

                            @endif


                    </div>
                    <div class="col-md-6">
                        @if($order->is_pushed==0)
                        <a type="button" href="{{route('push.all.lineitems',$order->id)}}" style="float: right" class="btn btn-primary btn-sm ">Push All</a>
                     @endif
                        <button style="float: right;display: none;" class="btn btn-primary btn-sm partial_btn mx-2">Partially Push</button>
                    </div>
                </div>
                <div class="row">

                <div class=" col-md-6 mx-5  order-details-time">
                    <div><p>{{ \Carbon\Carbon::parse($order->created_at)->format('F d, Y ')}} at {{ \Carbon\Carbon::parse($order->created_at)->format('g:i A')}} </p></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8" style="padding-right: 0">

                @foreach ($line_items as $partner_id => $items)


                <div class="card bg-white border-0 mt-3 mb-3 shadow-sm">
                    @php
                    $partner=\App\Models\Partner::find($partner_id);
                    $partner_name=$partner->name;
                    $count_items=$items->where('is_pushed',1)->count();
                    $is_pushed=(count($items)!=$count_items)?0:1;
                    @endphp

                    <div class="card-header row  text-dark">
                            <div class="col-6 d-flex">
                                @if(!$is_pushed)
                                <input style="margin-top: 4px;" class="form-check-input single_check" type="checkbox"  value="{{$partner_id}}">
                                @endif
                                    <h3 class="mx-2">{{$partner_name}}</h3>
                            </div>
                        <div class="col-6">
                            @if($is_pushed)
                            <span style="float: right;" class="badge bg-danger">Pushed</span>
                                @endif
                        </div>

                    </div>

                    <div class="card-body bg-white border-light">

                        @foreach($items as $lineitem)
                        <div class="row">

                                <div class="col-md-1">
                                    @if(isset($lineitem->product_varient->image) && $lineitem->product_varient->image!='')
                                        <img src="{{$lineitem->product_varient->image}}" style="width: 100%">
                                    @else
                                        <img src="{{asset('empty.jpg')}}" style="width: 100%">
                                    @endif
                                </div>



                            <div class="col-md-7">
                              <strong> {{$lineitem->title}}</strong>
                                <br>
                              {{$lineitem->variant_title}}
                                <br>
                                @if($lineitem->sku!=null)
                                SKU:{{$lineitem->sku}}
                                    @endif


                            </div>
                                @php
                                    $lineitem_total=$lineitem->price*$lineitem->quantity;

                                @endphp


                                <div class="col-md-2">
                                   {{$currency}} {{number_format($lineitem->price,2)}} x {{$lineitem->quantity}}

                                </div>

                                <div class="col-md-2 text-right">{{$currency}} {{number_format($lineitem_total,2)}}</div>
                                <hr>
                                <br>


                        </div>
                        @endforeach
                    </div>

                </div>
                @endforeach
                <div class="card bg-white border-0 mt-3 mb-3 shadow-sm">
                    <div class="card-body bg-white border-light">
                        <div class="row">
                            <div class="col-md-3">Subtotal</div>
                            <div class="col-md-3">{{$line_items_count}} Items</div>
                            <div class="col-md-6 text-right">{{$currency}} {{number_format($order->total_price,2)}}</div>




                            <div class="col-md-6 mt-2"><strong>Total</strong></div>
                            <div class="col-md-6 mt-2 text-right">{{$currency}} {{number_format($order->total_price,2)}} </div>
                        </div>
                    </div>

                </div>

            </div>
            <div class="col-lg-4 col-md-4 col-sm-4">
                <div class="card border-light border-0 mt-3  shadow-sm">
                    <div class="card-header  text-dark">
                        <h3>Note</h3>
                    </div>

                    <div class="card-body bg-white">
                        @if(isset($order->note))
                            <p>{{$order->note}}</p>
                        @else
                            <p>No Note</p>
                        @endif
                    </div>
                </div>

                <div class="mt-1">
                    <div class="card border-light border-0 mt-3  shadow-sm">
                        <div class="card-header  text-dark">
                            <h3>SHIPPING ADDRESS</h3>
                        </div>

                        <div class="card-body bg-white">
                            @if(isset($order->shipping_name))
                                <span>Name : {{$order->shipping_name}}</span>
                            @else
                                <span>No Name</span>
                            @endif
                            <br>
                            @if(isset($order->address1))
                                <span>Address1 : {{$order->address1}}</span>
                            @else
                                <span>No Address</span>
                            @endif
                            <br>
                            @if(isset($order->address2))

                                <span>Address2 : {{$order->address2}}</span>
                            @else
                                <span>No Address</span>
                            @endif
                            <br>
                            @if(isset($order->city) && $order->zip)
                                <span>City & Zip : {{$order->city}} {{$order->zip}}</span>
                            @else
                                <span>No Code</span>
                            @endif
                            <br>
                            @if(isset($order->country))
                                <span>Country : {{$order->country}}</span>
                            @else
                                <span>Not Defined</span>
                            @endif
                            <br>
                            @if(isset($order->province))
                                <span>Province : {{$order->province}}</span>
                            @else
                                <span>Not Defined Province</span>
                            @endif
                            <br>
                            @if(isset($order->phone) && $order->phone != '')
                                <span>Phone : {{$order->phone}}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                <form id="selected_ids_form" method="post" action="{{route('push.selected.lineitems')}}" >
                    @csrf
                    <input type="hidden" id="product_ids" name="partner_ids" value="">
                    <input type="hidden" id="order_id" name="order_id" value="{{$order->id}}">
                </form>
            </div>



            <script>
                $(document).ready(function (){

                    $('body').on('click','.single_check',function(){


                        if($('.single_check:checked').length >0){

                            $('.partial_btn').css('display','block');
                        }
                        else{
                            $('.partial_btn').css('display','none');
                        }
                        var val = [];
                        $('.single_check:checked').each(function(i){
                            val[i] = $(this).val();
                        });

                        var product_ids= val.join(',');
                        $('#product_ids').val(product_ids);

                    });

                    $('.partial_btn').click(function (){
                        $('#selected_ids_form').submit();
                    });
                    });
            </script>

@endsection
