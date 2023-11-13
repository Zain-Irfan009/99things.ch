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
                    Settings
                </h1>
            </div>
        </div>
    </div>
    </div>



    <div class="page-body">
        <div class="">
            <div class="col-12">
                <div class="card bg-white border-0 mt-3 mb-3 shadow-sm">
                    <div class="card-body bg-white border-light">

                        <form method="post" action="{{route('setting.save')}}">
                            @csrf
                            <div class="card">

                                <div class="card-body">
                                    <div class="row">


                                        <div class="col-lg-6">
                                            <label class="form-label">Price Multiplier</label>
                                            <input type="text"  value="@if($setting){{$setting->base_price_multiplier}}@endif" class="form-control mt-2" name="base_price_multiplier" placeholder="Price Multiplier">
                                        </div>

                                        <div class="col-lg-6">
                                            <label class="form-label">Compare at Price Multiplier</label>
                                            <input type="text"  value="@if($setting){{$setting->base_compare_at_price_multiplier}}@endif" class="form-control mt-2" name="base_compare_at_price_multiplier" placeholder="Compare At Price Multiplier">
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


@endsection
