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
        </div>
            <div class="row mt-2">
            <div class="col-md-12" >

                <div class="form-group">

                    <form action="{{route('partner.filter')}}" method="post">
                        @csrf

                        <div class="input-group">

                            <select class="form-control mx-2 " name="platform">
                                <option value="">Select Platform</option>
                                <option @if(isset($request) && $request->input('platform')=='Shopify') selected @endif value="Shopify">Shopify</option>
                                <option @if(isset($request) && $request->input('platform')=='Magento') selected @endif value="Magento">Magento</option>
                                <option @if(isset($request) && $request->input('platform')=='Woocommerce') selected @endif value="Woocommerce">Woocommerce</option>
                            </select>

                            <input placeholder="Enter Partner Name" type="text" @if (isset($request)) value="{{$request->partner_filter}}" @endif name="partner_filter" id="question_email" autocomplete="off" class="form-control">
                            @if(isset($request->partner_filter))
                                <a href="{{ route('partner')}}" type="button" class="btn btn-secondary clear_filter_data mr-1 pl-4 pr-4">Clear</a>
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
                                                <label class="form-label">Email</label>
                                                <input type="email" required class="form-control mt-2" name="email" placeholder="Partner Email">
                                            </div>

                                            <div class="col-lg-6 mt-2">
                                                <label class="form-label">Select Platform</label>
                                                <select class="form-control " required name="platform">
                                                    <option value="">Select Platform</option>
                                                    <option  value="Shopify">Shopify</option>
                                                    <option value="Magento">Magento</option>
                                                    <option value="Woocommerce">Woocommerce</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-6 mt-2">
                                                <label class="form-label">Shop Name</label>
                                                <input type="text" required class="form-control mt-2" name="shop_name" placeholder="Shop Name">
                                            </div>


                                            <div class="col-lg-6 mt-2">
                                                <label class="form-label">Token</label>
                                                <input type="text" required class="form-control mt-2" name="shopify_token" placeholder="Token">
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
    </div>



    <div class="page-body">
        <div class="">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="table-responsive">
                            @if (count($partners) > 0)
                            <table
                                class="table table-vcenter table-mobile-md card-table">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Shop Name</th>
                                    <th>Platform</th>
                                    <th>Active</th>
                                    <th class="w-1"></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($partners as $partner)
                                <tr>
                                    <td data-label="Name" >
                                        <div class="d-flex py-1 align-items-center">
                                            <div class="flex-fill">
                                                <div class="text-muted">{{$partner->name}}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Title" >

                                        <div class="text-muted">{{$partner->email}}</div>
                                    </td>
                                    <td class="text-muted" data-label="Role" >
                                    {{$partner->shop_name}}
                                    </td>

                                    <td>
                                        <span class="badge bg-success">{{$partner->platform}}</span>
                                    </td>

                                    <td>
                                        <label class="form-check form-switch mt-1 ">
                                       <input class="form-check-input ml-3 status_change" data-id="{{$partner->id}}" @if($partner->status==1) checked @endif value="1" type="checkbox" name="status">
                                      </label>
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <div class="dropdown">
                                                <button class="btn dropdown-toggle align-text-top" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="{{ route('view.partner', $partner->id)}}">
                                                       View
                                                    </a>

                                                    <a class="dropdown-item" href="{{ route('sync.partner.products', $partner->id)}}">
                                                        Sync Products
                                                    </a>
                                                    <a class="dropdown-item delete_btn"   data-confirm="Are you sure you want to delete this item?" href="{{ route('delete.partner', $partner->id)}}">
                                                        Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
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


    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
