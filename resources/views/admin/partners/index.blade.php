@extends('admin.layout.index')

<style>
    .anchor_click{
        text-decoration: none !important;
    }
    .font-weight-medium{
        color: black;
    }
</style>
@section('content')


    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">

                <h1 class="page-title">
                    Partners
                </h1>
            </div>

            <div class="col-md-6" >

                <div class="form-group">

                    <form action="" method="get">


                        <div class="input-group">
                            <input placeholder="Enter Partner Name" type="text" @if (isset($product_filter)) value="{{$product_filter}}" @endif name="products_filter" id="question_email" autocomplete="off" class="form-control">
                            @if(isset($product_filter))
                                <a href="{{ route('all.products')}}" type="button" class="btn btn-secondary clear_filter_data mr-1 pl-4 pr-4">Clear</a>
                            @endif
                            <button type="submit" class="btn btn-primary mr-1 pl-4 mx-2 pr-4">Filter</button>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#modal-export" type="button" class="btn sync-button btn-primary ml-1">Add Partner</a>
                        </div>
                    </form>

                    <div class="modal modal-blur fade" id="modal-export" tabindex="-1" data-focus="false"   role="dialog" aria-hidden="true">
                        <form method="post" action="{{route('save.partner')}}">
                            @csrf
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <input type="hidden" value="" name="campaign_id">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Add Partner</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <input type="hidden" class="timezone" name="timezone">
                                    <div class="modal-body">
                                        <div class="row">

                                            <div class="col-lg-6">
                                                <label class="form-label">Name</label>
                                                <input type="text" required class="form-control mt-2" name="name" placeholder="Partner Name">
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="form-label">Shop Name</label>
                                                <input type="text" required class="form-control mt-2" name="shop_name" placeholder="Shop Name">
                                            </div>

                                            <div class="col-lg-6 mt-2">
                                                <label class="form-label">Shopify Domain</label>
                                                <input type="text" required class="form-control mt-2" name="shopify_domain" placeholder="Shopify Domain">
                                            </div>
                                            <div class="col-lg-6 mt-2">
                                                <label class="form-label">Shopify Token</label>
                                                <input type="text" required class="form-control mt-2" name="shopify_token" placeholder="Shopify Token">
                                            </div>


                                            <div class="col-lg-6 mt-2">
                                                <label class="form-label">API Key</label>
                                                <input type="text" required class="form-control mt-2" name="api_key" placeholder="API Key">
                                            </div>
                                            <div class="col-lg-6 mt-2">
                                                <label class="form-label">API Secret</label>
                                                <input type="text" required class="form-control mt-2" name="api_secret" placeholder="API Secret">
                                            </div>



                                        </div>

                                    </div>

                                    <div class="modal-footer mt-1">
                                        <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                                            Cancel
                                        </a>
                                        <button  type="submit" class="btn btn-primary ms-auto" >
                                            Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
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

                            @if (count($partners) > 0)
                                <table
                                    class="table table-vcenter card-table">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Shop Name</th>
                                        <th>Shopify Domain</th>
                                        <th class="w-1">Action</th>

                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($partners as $partner)
                                        <tr>

                                            <td class="alignment">{{$partner->name}}</td>
                                            <td class="alignment">{{$partner->shop_name}}</td>
                                            <td class="alignment">{{$partner->shopify_domain}}</td>
                                            <td>


{{--                                                <a href="{{ route('product.view', $partner->id)}}" class="btn btn-primary view">View</a>--}}
                                            </td>


                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                            @else
                                <h3 class="mx-3 my-3">No Partner Found</h3>
                            @endif

                            <div class="pagination">
                                {{ $partners->appends(\Illuminate\Support\Facades\Request::except('page'))->links("pagination::bootstrap-4") }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
