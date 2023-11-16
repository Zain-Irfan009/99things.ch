@extends('admin.layout.index')
<link rel="stylesheet" href="{{asset('')}}richtexteditor/rte_theme_default.css">
<style>
    .options{
        font-size: 12px !important;
    }
    .size_button{
        font-size: 13px !important;
    }
    .error-alert{
        background: #d94343 !important;
        color: white !important;
    }
    .error_icon{
        color: white !important;
    }
</style>
@section('content')

    <div class="container">
    <div class="row row-cards ">


        <div class="col-lg-12 col-md-12">


            <div class="">
                <div class="row">
                    <div class="col-8">
                <h1 class="page-title">
                    <div class="custom-left-arrow-div " >
                        <a style="text-decoration: none; padding:12px; font-size: 25px; color: black;" href="{{route('partner')}}"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
                    </div>
                    {{$partner->name}}({{$partner->shop_name}})

                </h1>
                </div>
                </div>


                <div class="row">
                    <div class="col-sm-12" >

                        <div class="card bg-white border-0 mt-3 mb-3 shadow-sm">
                            <div class="card-header">
                                <h4 class="card-title">Basic Details</h4>
                            </div>
                            <div class="card-body bg-white border-light">
                                <form method="post" action="{{route('partner.setting.save')}}">
                                    @csrf
                                <div class="card">
                                    <input type="hidden" name="partner_id" value="{{$partner->id}}">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <label class="form-label">Name</label>
                                                <input type="text"  value="{{$partner->name}}" class="form-control mt-2" name="name" placeholder="Partner Name">
                                            </div>

                                        <div class="col-lg-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" disabled value="{{$partner->email}}" class="form-control mt-2" name="email" placeholder="Partner Email">
                                        </div>

                                            <div class="col-lg-6 mt-2 ">
                                                <label class="form-label">Platform</label>
                                                <select class="form-control ">
                                                        <option  value="{{$partner->platform}}">{{$partner->platform}}</option>
                                                </select>
                                            </div>


                                            <div class="col-lg-6 mt-2 ">
                                            <label class="form-label">Store Name</label>
                                            <input type="text" disabled value="{{$partner->shop_name}}" class="form-control mt-2" name="shop_name" placeholder="Shop Name">
                                        </div>



                                            <div class="col-lg-6 mt-2 ">
                                                <label class="form-label">Store Language</label>
                                                <select class="form-control " required name="store_language">
                                                    <option value="">Select Store Language</option>
                                                    @foreach($languages as $language)
                                                        <option @if($partner->store_language_id==$language->id) selected @endif  value="{{$language->id}}">{{$language->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>


                                        <div class="col-lg-6 mt-2">
                                            <label class="form-label">Token</label>
                                            <input type="text"  value="{{$partner->shopify_token}}" class="form-control mt-2" name="shopify_token" placeholder="Shopify Token">
                                        </div>


                                        <div class="col-lg-6 mt-2">
                                            <label class="form-label">API Key</label>
                                            <input type="text"  value="{{$partner->api_key}}" class="form-control mt-2" name="api_key" placeholder="API Key">
                                        </div>
                                        <div class="col-lg-6 mt-2">
                                            <label class="form-label">API Secret</label>
                                            <input type="text"  value="{{$partner->api_secret}}" class="form-control mt-2" name="api_secret" placeholder="API Secret">
                                        </div>

                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6"></div>
                                            <div class="col-6">

                                                <button style="float: right"  type="submit" class="btn btn-primary ms-auto" >
                                                    Save
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                </form>

                            </div>

                        </div>


                    </div>

                </div>
                <div class="row">

                    <div class="col-sm-12" >

                        <div class="card bg-white border-0 mt-3 mb-3 shadow-sm">
                            <div class="card-header">
                                <h4 class="card-title">Auto-Push Settings</h4>
                            </div>
                            <div class="card-body bg-white border-light">

                                <form method="post" action="{{route('partner.auto_push.setting.save')}}">
                                    @csrf
                                <div class="card">

                                    <div class="card-body">
                                        <div class="row">

                                            <input type="hidden" name="partner_id" value="{{$partner->id}}">

                                            <div class="col-6">
                                                <label class="form-check form-switch " >
                                                    <input class="form-check-input ml-3 " data-id="{{$partner->id}}" @if($partner->autopush_products==1) checked @endif value="1" type="checkbox" name="autopush_products">
                                                    <strong>Auto-Push Products</strong>
                                                </label>
                                            </div>

                                            <div class="col-6">
                                                <label class="form-check form-switch ">
                                                    <input class="form-check-input ml-3 " data-id="{{$partner->id}}" @if($partner->autopush_orders==1) checked @endif value="1" type="checkbox" name="autopush_orders">
                                                    <strong>Auto-Push Orders</strong>
                                                </label>
                                            </div>

                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-6"></div>
                                            <div class="col-6">
                                            <button style="float: right"  type="submit" class="btn btn-primary ms-auto" >
                                                Save
                                            </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                </form>

                            </div>

                        </div>


                    </div>
                    <div class="col-sm-12" >

                        <div class="card bg-white border-0 mt-3 mb-3 shadow-sm">
                            <div class="card-header">
                                <h4 class="card-title">Multiplier Settings</h4>
                            </div>
                            <div class="card-body bg-white border-light">

                                <form method="post" action="{{route('partner.multiplier.setting.save')}}">
                                    @csrf
                                <div class="card">

                                    <div class="card-body">
                                        <div class="row">

                                            <input type="hidden" name="partner_id" value="{{$partner->id}}">

                                        <div class="col-lg-6">
                                            <label class="form-label">Price Multiplier</label>
                                            <input type="text"  value="{{$partner->price_multiplier}}" class="form-control mt-2" name="price_multiplier" placeholder="Price Multiplier">
                                        </div>

                                            <div class="col-lg-6">
                                                <label class="form-label">Compare at Price Multiplier</label>
                                                <input type="text"  value="{{$partner->compare_at_price_multiplier}}" class="form-control mt-2" name="compare_at_price_multiplier" placeholder="Compare At Price Multiplier">
                                            </div>

                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-6"></div>
                                            <div class="col-6">
                                            <button style="float: right"  type="submit" class="btn btn-primary ms-auto" >
                                                Save
                                            </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                </form>

                            </div>

                        </div>


                    </div>

                </div>
            </div>

        </div>
        </div>

    </div>

    <script src="{{asset('')}}richtexteditor/rte.js"></script>
    <script src="{{asset('')}}richtexteditor/plugins/all_plugins.js"></script>

    <script>

        $(document).ready(function(){
            $('.status_change').change(function (){


                var id= $(this).data('id');

                if($(this).is(':checked')){

                    var status=1;
                }
                else{
                    var status=0;
                }

                $.ajax({
                    type:'get',
                    url:'{{URL::to('partner-status-change')}}',
                    data:{'status':status,'id':id},

                    success:function(data){
                        var op=' ';
                        if(data.status==1){
                            toastr.success("Partner Active Successfully!!");
                        }
                        else{
                            toastr.success("Partner Disable Successfully!!");

                        }

                    },
                    error: function (request, status, error) {


                    }


                });
            });

            setTimeout(function() { $(".alert-success").hide(); }, 2000);
            setTimeout(function() { $(".error-alert").hide(); }, 2000);


            $('.coupon_code_limit').prop('required', false);
            $('.referral_amount').prop('required', false);
            $('.coupon_discount_amount').prop('required', false);


            $('.select_sms').change(function (){

                var sms_type=$(this).val();

                if(sms_type=='general'){
                    $('.custom_sms').hide();
                    $('.write_sms').show();
                    $('.coupon_code_limit').prop('required', false);
                    $('.referral_amount').prop('required', false);
                    $('.coupon_discount_amount').prop('required', false);
                }
                else{
                    $('.write_sms').hide();
                    $('.custom_sms').show();
                    $('.coupon_code_limit').prop('required', true);
                    $('.referral_amount').prop('required', true);
                    $('.coupon_discount_amount').prop('required', true);
                }
            });
        });
    </script>
@endsection
