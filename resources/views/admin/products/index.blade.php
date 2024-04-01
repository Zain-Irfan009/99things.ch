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
    .table-responsive{
        min-height: 320px;
    }
    .table-vcenter{
        font-size: 14px;
    }
</style>
@section('content')


    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">

                <h1 class="page-title">
                  Products
                </h1>
            </div>

            <div class="col-md-6" style="text-align: right">
            <a href="{{route('approve.all')}}" class="btn btn-success btn-sm">Approve All</a>
            <a href="{{route('deny.all')}}" class="btn btn-danger btn-sm">Deny All</a>
            </div>
        </div>
            <div class="row mt-2">
            <div class="col-md-4" >
                <form>
                <div class="form-floating mb-3">
                    <input type="text" value="{{Request::get('search')}}" id='search' class="form-control" name="search" placeholder="Search Products" >
                    <span class="input-icon-addon" onclick="filterByName()">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <circle cx="10" cy="10" r="7" />
            <line x1="21" y1="21" x2="15" y2="15" />
        </svg>
    </span>
                    <label for="floating-input">Search Product</label>
                </div>
                </form>


            </div>
                <div class="col-md-2">
                    <div class="form-floating ">
                        <select class="form-select" aria-label="Default select example" onchange='filterByPartner(this.value)'>
                            <option value='' selected="">partner</option>
                            @foreach($partners as $partner)
                                <option value="{{$partner->id}}" {{Request::get('partner') == $partner->id  ? 'selected' : ''}}>{{$partner->name}}</option>
                            @endforeach
                        </select>
                        <label for="floating-input">Select</label>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-floating ">
                        <select class="form-select" aria-label="Default select example" onchange='filterByStatus(this.value)'>
                            <option value=''  selected="">App Status</option>
                            <option value="0" {{ Request::get('status') == "0" ? 'selected' : '' }}>Pending</option>
                            <option value="1" {{ Request::get('status') == "1" ? 'selected' : '' }}>Approved</option>
{{--                            <option value="2" {{ Request::get('status') == "2" ? 'selected' : '' }}>Changes Pending</option>--}}
                            <option value="3" {{ Request::get('status') == "3" ? 'selected' : '' }}>Deny</option>

                        </select>
                        <label for="floating-input">Select</label>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-floating ">
                        <select class="form-select" aria-label="Default select example" onchange='filterByShopifyStatus(this.value)'>
                            <option value=''  selected="">Shopify Status</option>
                            <option value="Pending" {{ Request::get('shopify_status') == "Pending" ? 'selected' : '' }}>Pending</option>
                            <option value="Complete" {{ Request::get('shopify_status') == "Complete" ? 'selected' : '' }}>Completed</option>
                            <option value="In-Progress" {{ Request::get('shopify_status') == "In-Progress" ? 'selected' : '' }}>In-Progress</option>
                            <option value="Failed" {{ Request::get('shopify_status') == "Failed" ? 'selected' : '' }}>Failed</option>
                        </select>
                        <label for="floating-input">Select</label>
                    </div>
                </div>

                <div class="col-md-2">
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
                    <button data-action="approve" class="btn btn-success submit_btn btn-sm">Approve Selected</button>
                    <button data-action="deny" class="btn btn-danger btn-sm submit_btn">Deny Selected</button>
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

                            @if (count($products) > 0)
                                <table
                                    class="table table-vcenter card-table">
                                    <thead>
                                    <tr>

                                        <th><input class="form-check-input" id="checkAll" value=""  type="checkbox"></th>
                                        <th style="width: 10%">Preview</th>
                                        <th style="width: 20%">Product</th>
                                        <th>Date</th>
                                        <th scope="col">Partner Name</th>
                                        <th>App Status</th>
                                        <th>Shopify Status</th>
                                        <th>Action</th>

                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($products as $product)
                                        <tr>

                                            <td>
                                                <input class="form-check-input single_check" type="checkbox"  value="{{$product->id}}"></td>
                                            <td>

                                                <a href="{{url('product-view')}}/{{$product->id}}">
                                                    @if($product->featured_image)
                                                    <img src="{{$product->featured_image}}" width="75px" alt="">
                                                        @else
                                                        <img src="{{asset('empty.jpg')}}" width="75px" alt="">
                                                    @endif
                                                </a>

                                            </td>
{{--                                            <td class="" style="vertical-align: middle;"><a href="#">@if($product->featured_image != null)<img src="{{$product->featured_image}}" width="40px" height="40px">@else <img src="{{asset('empty.jpg')}}" width="40px" height="40px"> @endif</a></td>--}}
                                            <td class="alignment"><a href="{{url('product-view')}}/{{$product->id}}">{{ substr($product->title, 0, 30) }}</a></td>
                                            <td>{{ date('M d, Y', strtotime($product->updated_at)) }}</td>
                                            <td>@if($product->has_partner){{$product->has_partner->name}}@endif</td>

                                            <td>@if($product->app_status==1) <span class="badge bg-success">Approved</span> @elseif($product->app_status=='2') <span class="badge bg-warning">Changes Done</span> @elseif($product->app_status=='3') <span class="badge bg-danger">Deny</span> @else <span class="badge bg-primary">Pending </span>@endif</td>
                                            <td>@if($product->shopify_status=='Complete') <span class="badge bg-success">Completed</span> @elseif($product->shopify_status=='Deny') <span class="badge bg-danger">Deny</span> @else <span class="badge bg-primary">Pending </span>@endif</td>
                                            <td>
                                                <div class="btn-list flex-nowrap">
                                                    <div class="dropdown">
                                                        <button class="btn dropdown-toggle align-text-top" data-bs-toggle="dropdown">
                                                            Actions
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            @if($product->app_status!=1)
                                                            <a class="dropdown-item" href="{{url('shopify-create')}}/{{$product->id}}">Approve Product</a>
                                                                @endif
                                                            @if($product->app_status==1)
                                                            <a class="dropdown-item delete_btn" data-confirm="Are you sure you want to delete this Partner?" href="{{url('reject-product')}}/{{$product->id}}">Deny Product</a>
                                                                @endif
                                                            <a class="dropdown-item "  href="{{url('update-product-platform')}}/{{$product->id}}">Update Product</a>
                                                            <a class="dropdown-item "  href="{{url('product-view')}}/{{$product->id}}">View Product</a>
                                                        </div>
                                                    </div>
                                                </div>


                                            </td>


                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                            @else
                                <h3 class="mx-3 my-3">No Product Found</h3>
                            @endif

                            <div class="pagination">
                                {{ $products->appends(\Illuminate\Support\Facades\Request::except('page'))->links("pagination::bootstrap-4") }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <form id="selected_ids_form" method="post" action="{{route('update.selected.products')}}" >
        @csrf
        <input type="hidden" id="product_ids" name="product_ids" value="">
        <input type="hidden" id="action" name="action" value="">
    </form>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

        function filterByPartner(id)
        {
            var search='{{Request::get('search')}}';
            var date='{{Request::get('date')}}';
            var status='{{Request::get('status')}}';
            var shopify_status='{{Request::get('shopify_status')}}';
            window.location.href='products?search='+search+'&partner='+id+'&date='+date+'&status='+status+'&shopify_status='+shopify_status;
        }

        function filterByStatus(id)
        {

            var search='{{Request::get('search')}}';
            var partner='{{Request::get('partner')}}';
            var date='{{Request::get('date')}}';
            var shopify_status='{{Request::get('shopify_status')}}';
            window.location.href='products?search='+search+'&partner='+partner+'&date='+date+'&status='+id+'&shopify_status='+shopify_status;
        }

        function filterByShopifyStatus(val)
        {

            var search='{{Request::get('search')}}';
            var partner='{{Request::get('partner')}}';
            var date='{{Request::get('date')}}';
            var status='{{Request::get('status')}}';

            window.location.href='products?search='+search+'&partner='+partner+'&date='+date+'&status='+status+'&shopify_status='+val;
        }

        function filterByName(val)
        {
            var search=$('#search').val();
            if(search!='')
            {
                var partner='{{Request::get('partner')}}';
                var date='{{Request::get('date')}}';
                var status='{{Request::get('status')}}';
                var shopify_status='{{Request::get('shopify_status')}}';
                window.location.href='products?search='+search+'&partner='+partner+'&date='+date+'&status='+status+'&shopify_status='+shopify_status;
            }
        }
        function filterByDate(val)
        {
            if(val!='')
            {
                var search='{{Request::get('search')}}';
                var partner='{{Request::get('partner')}}';
                var status='{{Request::get('status')}}';
                var shopify_status='{{Request::get('shopify_status')}}';
                window.location.href='products?search='+search+'&partner='+partner+'&date='+val+'&status='+status+'&shopify_status='+shopify_status;
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

            $(document).on('click', '.delete_btn', function (e) {
                e.preventDefault();

                var deleteLink = $(this).attr('href');
                var confirmationMessage = $(this).data('confirm');

                Swal.fire({
                    title: 'Are you sure?',
                    text: confirmationMessage,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteLink;
                    }
                });
            });

        });
    </script>
@endsection
