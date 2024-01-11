@extends('admin.layout.index')

<style>
    .anchor_click{
        text-decoration: none !important;
    }
    .font-weight-medium{
        color: black;
    }
    .input-icon-addon{
        right: 0 !important;
        left:unset !important;
    }
    .table-vcenter{
        font-size: 14px !important;
    }
    .table-responsive{
        min-height: 320px;
    }
</style>
@section('content')


    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">

                <h1 class="page-title">
                    Orders
                </h1>
            </div>

            <div class="col-md-6" style="text-align: right">
                <a href="{{route('push.all.orders')}}" class="btn btn-success btn-sm">Push All</a>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6" >
                <form>
                    <div class="form-floating mb-3">
                        <input type="text" value="{{Request::get('search')}}" id='search' class="form-control" name="search" placeholder="Search Order" >
                        <span class="input-icon-addon" onclick="filterByName()">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <circle cx="10" cy="10" r="7" />
            <line x1="21" y1="21" x2="15" y2="15" />
        </svg>
    </span>
                        <label for="floating-input">Search Order</label>
                    </div>
                </form>


            </div>
            <div class="col-md-3">
                <div class="form-floating ">
                    <select class="form-select" aria-label="Default select example" onchange='filterByStatus(this.value)'>
                        <option value='' selected="">Status</option>
                        <option value="0" {{ Request::get('status') == "0" ? 'selected' : '' }}>Not Pushed</option>
                        <option value="1" {{ Request::get('status') == "1" ? 'selected' : '' }}>Pushed</option>

                    </select>
                    <label for="floating-input">Select</label>
                </div>
            </div>





            <div class="col-md-3">
                <div class="label-area sort-area">
                    <div class="mb-3">

                        <div class="form-floating mb-3">
                            <input class="form-control" type="date" value="{{$request->date}}" onblur='filterByDate(this.value)' id="datepicker-icon" />

                            <label for="floating-input">Date</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mt-3 selected_btn" style="text-align: right;display: none">
                <button data-action="approve" class="btn btn-success submit_btn btn-sm">Push Selected</button>
            </div>
        </div>
    </div>
    </div>



    <div class="page-body">
        <div class="">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="table-responsive">

                            @if (count($orders) > 0)
                                <table
                                    class="table table-vcenter card-table">
                                    <thead>
                                    <tr>

                                        <th><input class="form-check-input" id="checkAll" value=""  type="checkbox"></th>
                                        <th>Order</th>
                                        <th>Date</th>
                                        <th scope="col">Customer</th>
                                        <th>Total</th>
                                        <th>Payment Status</th>
                                        <th>Fulfillment Status</th>
                                        <th>Status</th>
                                        <th>Action</th>

                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($orders as $order)
                                        <tr>

                                            <td>
                                                @if($order->is_pushed==0)
                                                <input class="form-check-input single_check" type="checkbox"  value="{{$order->id}}">
                                            @endif
                                            </td>

                                            <td class="alignment"><a href="{{url('order-view')}}/{{$order->id}}">{{ $order->order_number }}</a></td>
                                            <td>{{ date('M d, Y', strtotime($order->created_at)) }}</td>
                                            <td>{{$order->first_name}} {{$order->last_name}}</td>
                                            <td>{{$currency}} {{$order->total_price}}</td>
                                            <td><span class="badge bg-warning">{{$order->financial_status}}</span></td>
                                            @if($order->fulfillment_status==null)
                                                <td><span class="badge bg-danger">Unfulfilled</span></td>
                                            @else
                                                <td><span class="badge bg-blue">{{$order->fulfillment_status}}</span></td>
                                            @endif

                                            <td>@if($order->is_pushed==1)<span class="badge bg-success">Pushed</span>@else <span class="badge bg-danger">Not Pushed</span> @endif </td>

                                            <td>
                                                <div class="btn-list flex-nowrap">
                                                    <div class="dropdown">
                                                        <button class="btn dropdown-toggle align-text-top" data-bs-toggle="dropdown">
                                                            Actions
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            @if($order->is_pushed==0)
                                                            <a class="dropdown-item "  href="{{route('push.all.lineitems',$order->id)}}">Push Order</a>
                                                         @endif
                                                            <a class="dropdown-item "  href="{{url('order-view')}}/{{$order->id}}">View Order</a>
                                                        </div>
                                                    </div>
                                                </div>


                                            </td>


                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                            @else
                                <h3 class="mx-3 my-3">No Order Found</h3>
                            @endif

                            <div class="pagination">
                                {{ $orders->appends(\Illuminate\Support\Facades\Request::except('page'))->links("pagination::bootstrap-4") }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <form id="selected_ids_form" method="post" action="{{route('push.selected.orders')}}" >
        @csrf
        <input type="hidden" id="product_ids" name="order_ids" value="">
        <input type="hidden" id="action" name="action" value="">
    </form>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>



        function filterByStatus(id)
        {

            var search='{{Request::get('search')}}';
            var partner='{{Request::get('partner')}}';
            var date='{{Request::get('date')}}';

            window.location.href='orders?search='+search+'&date='+date+'&status='+id;

        }



        function filterByName(val)
        {
            var search=$('#search').val();
            if(search!='')
            {
                var date='{{Request::get('date')}}';
                var status='{{Request::get('status')}}';

                window.location.href='orders?search='+search+'&date='+date+'&status='+status;

            }
        }
        function filterByDate(val)
        {
            if(val!='')
            {
                var search='{{Request::get('search')}}';
                var status='{{Request::get('status')}}';

                window.location.href='orders?search='+search+'&date='+val+'&status='+status;
            }
        }
    </script>

    <script>
        $(document).ready(function (){

            $('body').on('click','.single_check',function(){


                if($('.single_check:checked').length >0){

                    $('.selected_btn').css('display','block');
                }
                else{
                    $('.selected_btn').css('display','none');
                }
                var val = [];
                $('.single_check:checked').each(function(i){
                    val[i] = $(this).val();
                });

                var product_ids= val.join(',');
                $('#product_ids').val(product_ids);

            });

            $("#checkAll").change(function(){

                if($('#checkAll').prop('checked')) {
                    $('.single_check').prop('checked', true)
                    $('.selected_btn').css('display','block');
                    var val = [];
                    $('.single_check:checked').each(function(i){
                        val[i] = $(this).val();
                    });

                    var product_ids= val.join(',');
                    $('#product_ids').val(product_ids);

                }else {
                    $('.single_check').prop('checked', false);
                    $('.selected_btn').css('display','none');
                }
            });


            $('.submit_btn').click(function (){
                var text= $(this).data('action');
                $('#action').val(text);
                $('#selected_ids_form').submit();
            });


        });
    </script>
@endsection
