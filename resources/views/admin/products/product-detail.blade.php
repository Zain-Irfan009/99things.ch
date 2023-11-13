@extends('admin.layout.index')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css"
      integrity="sha512-xmGTNt20S0t62wHLmQec2DauG9T+owP9e6VU8GigI0anN7OXLip9i7IwEhelasml2osdxX71XcYm6BQunTQeQg=="
      crossorigin="anonymous"/>

<style>
    .options{
        font-size: 12px !important;
    }

    .rte-modern.rte-desktop.rte-toolbar-default{
        min-width: unset !important;
    }
    .hr_tag{
        margin-top: 0px;
    }
    .bootstrap-tagsinput{
        width: 100%;
    }
    .label-info{
        background-color: #17a2b8;

    }
    .label {
        display: inline-block;
        padding: .25em .4em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: .25rem;
        transition: color .15s ease-in-out,background-color .15s ease-in-out,
        border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
</style>
@section('content')


    <div class="row row-cards">
        <div class="col-lg-12 col-md-12">
            <form method="post" id="form1" action="{{route('update.product.detail')}}">
                @csrf
            <div class="">


                <div class="col-md-12 card card-border-radius pt-3 pb-2">
                    <div class="">
                        <div class="col-md-12 d-flex">
                            <div class="custom-left-arrow-div " >
                                <a style="text-decoration: none; padding:19px; font-size: 25px; color: black;" href="{{route('products')}}"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
                            </div>
                            <div><h2 style="margin-top: 3px;">{{$product->title}}</h2></div>

                        </div>


                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-10"></div>
                    <div class="col-2" style="text-align: right">
{{--                        <a href="#" data-bs-toggle="modal" data-bs-target="#modal-edit-product" type="button" class="btn btn-primary ">Edit</a>--}}
                        <button  type="submit" class="btn btn-primary ">Save</button>
                    </div>
                    <div class="modal modal-blur fade" id="modal-edit-product" tabindex="-1" data-focus="false"   role="dialog" aria-hidden="true">
                        <form method="post" action="{{route('update.product.detail')}}">
                            @csrf
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <input type="hidden" value="{{$product->id}}" name="id">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Product</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>


                                    <div class="modal-body">
                                        <div class="row">

                                            <div class="col-lg-12">
                                                <label class="form-label">Title</label>
                                                <input type="text" required value="{{$product->title}}" class="form-control mt-2" name="title" placeholder="Partner Name">
                                            </div>

                                            <div class="col-lg-12 mt-2">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" readonly name="description" id="editor2"   rows="10">{{$product->description}}</textarea>
                                            </div>

                                            <div class="col-lg-12 mt-2">
                                                <label class="form-label">Tags</label>
                                                <input type="text" data-role="tagsinput" name="tags" value="{{$product->tags}}" class="form-control">
                                            </div>




                                        </div>

                                    </div>

                                    <div class="modal-footer mt-1">
                                        <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                                            Cancel
                                        </a>
                                        <button  type="submit" class="btn btn-primary ms-auto" >
                                            Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
                <div class="row">
                    <div class="col-sm-8" style="padding-right: 0">

                        <div class="card bg-white border-0 mt-3 mb-3 shadow-sm">
                            <div class="card-body bg-white border-light">

                                <div class="form-group">
                                    <label class="col-form-label" for="formGroupExampleInput">Title</label>
                                    <input type="text" name="title"  class="form-control" id="formGroupExampleInput" value="{{$product->title}}">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label" for="description">Description</label>
                                    <textarea class="form-control"  name="description" id="editor1"   rows="10">{{$product->description}}</textarea>
                                </div>
                            </div>

                        </div>

                        @if(count($product_images) > 0)
                        <div class="card bg-white border-0 mt-3 mb-3 shadow-sm">
                            <div class="card-body bg-white border-light">
                                <strong>Media</strong>
                                <div class="row">
                                    @foreach($product_images as $product_image)
                                        <div class="col-md-3 mt-2">
                                            <td class="" style="vertical-align: middle;"><a href="#">@if($product_image->image)<img src="{{$product_image->image}}" width="80%" >@else <img src="{{asset('empty.jpg')}}" width="40px" height="40px"> @endif</a></td>
                                        </div>
                                    @endforeach


                                </div>
                            </div>

                        </div>
                            @endif


                        <div class="card bg-white border-0 mt-3 mb-3 shadow-sm">
                            <div class="card-body bg-white border-light">
                                <strong>Variants</strong>
                                <hr>
                                @foreach($product_options as $option)
                                    <div class="row">

                                        <div class="col-md-8">
                                            <h4 class="ml-4"><strong>{{$option->name}}</strong></h4>
                                            @foreach($option->values as $value)
                                                <span class="badge bg-cyan-lt options ">{{$value}}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <hr>
                                @endforeach




                            </div>

                        </div>
                        <div class="card bg-white border-0 mt-3 mb-3 shadow-sm">
                            <div class="card-header">
                              <strong>Variants</strong>
                            </div>
                            <div class="card-body bg-white border-light">


                                <table
                                    class="table table-vcenter table-mobile-md card-table">
                                    <thead>
                                    <tr>
                                        <th>Preview</th>
                                        <th style="width: 14%;">Title</th>
                                        <th style="width: 20%;">SKU</th>
                                        <th>Weight(Kg)</th>
                                        <th style="width: 14%;">Orignal Price</th>
                                        <th style="width: 14%;">New Price</th>
                                        <th style="width: 14%;">Origanal Compare At Price</th>
                                        <th style="width: 14%;">New Compare At Price</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($product_variants as $product_variant)
                                    <tr>
                                        <td>
                                            @if($product_variant->image)
                                                <img src="{{$product_variant->image}}" width="40px" height="40px" >
                                            @else <img src="{{asset('empty.jpg')}}" width="40px" height="40px">
                                            @endif
                                        </td>
                                        <td>
                                            <input type="text" disabled class="form-control" value="{{$product_variant->title}}">
                                        </td>

                                        <td>
                                            <input type="text" disabled class="form-control" value="{{$product_variant->sku}}">
                                        </td>
                                        <td>
                                            <input type="text" disabled class="form-control" value="{{$product_variant->weight}}">
                                        </td>

                                        <td>
                                            <input type="text" disabled class="form-control" value="{{number_format($product_variant->price,2)}}">
                                        </td>

                                        <td>
                                            <input type="text" disabled class="form-control" value="{{number_format($product_variant->price*$price_multiplier,2)}}">
                                        </td>

                                        <td>
                                            <input type="text" disabled class="form-control" value="{{number_format($product_variant->compare_at_price,2)}}">
                                        </td>
                                        <td>
                                            <input type="text" disabled class="form-control" value="{{number_format($product_variant->compare_at_price*$compare_at_price_multiplier,2)}}">
                                        </td>

                                    </tr>
                                    @endforeach

                                    </tbody>
                                </table>


{{--                                @foreach($product_variants as $product_variant)--}}
{{--                                    <div class="row">--}}

{{--                                        <div class="col-md-12">--}}

{{--                                            <div class="row">--}}
{{--                                                <div class="col-10"></div>--}}
{{--                                                <div col-2 style="text-align: right">--}}

{{--                                                    <a  href="#" data-bs-toggle="modal" data-bs-target="#modal-edit_{{$product_variant->id}}" type="button" class="btn btn-primary btn-sm">Edit</a>--}}
{{--                                                </div>--}}

{{--                                                <div class="modal modal-blur fade" id="modal-edit_{{$product_variant->id}}" tabindex="-1" data-focus="false"   role="dialog" aria-hidden="true">--}}
{{--                                                    <form method="post" action="{{route('update.variant.detail')}}">--}}
{{--                                                        @csrf--}}
{{--                                                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">--}}
{{--                                                            <div class="modal-content">--}}
{{--                                                                <input type="hidden" value="{{$product_variant->id}}" name="variant_id">--}}
{{--                                                                <div class="modal-header">--}}
{{--                                                                    <h5 class="modal-title">Edit Detail</h5>--}}
{{--                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}
{{--                                                                </div>--}}


{{--                                                                <div class="modal-body">--}}
{{--                                                                    <div class="row">--}}

{{--                                                                        <div class="col-lg-6">--}}
{{--                                                                            <label class="form-label">Variant Title</label>--}}
{{--                                                                            <input type="text" required class="form-control mt-2" value="{{$product_variant->title}}" name="title" placeholder="Variant Title">--}}
{{--                                                                        </div>--}}

{{--                                                                        <div class="col-lg-6">--}}
{{--                                                                            <label class="form-label">Price</label>--}}
{{--                                                                            <input type="number" step="0.01" required class="form-control mt-2" value="{{$product_variant->price}}" name="price" placeholder="Price">--}}
{{--                                                                        </div>--}}

{{--                                                                    </div>--}}

{{--                                                                </div>--}}

{{--                                                                <div class="modal-footer mt-1">--}}
{{--                                                                    <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">--}}
{{--                                                                        Cancel--}}
{{--                                                                    </a>--}}
{{--                                                                    <button  type="submit" class="btn btn-primary ms-auto" >--}}
{{--                                                                        Update--}}
{{--                                                                    </button>--}}
{{--                                                                </div>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </form>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}

{{--                                            <div class="row mt-3">--}}
{{--                                                <div class="col-1">--}}
{{--                                                    @if($product_variant->image)--}}
{{--                                                        <img src="{{$product_variant->image}}" width="100%" >--}}
{{--                                                    @else <img src="{{asset('empty.jpg')}}" width="40px" height="40px">--}}
{{--                                                    @endif--}}
{{--                                                </div>--}}

{{--                                                <div class="col-4">--}}
{{--                                                    <strong>{{$product_variant->title}}</strong>--}}
{{--                                                    <p>{{$product_variant->sku}}</p>--}}
{{--                                                </div>--}}

{{--                                                <div class="col-3">--}}
{{--                                                    <p><strong>Weight:</strong> {{$product_variant->weight}}{{$product_variant->weight_unit}}</p>--}}
{{--                                                    <p><strong>Available:</strong> {{$product_variant->stock}}</p>--}}
{{--                                                </div>--}}

{{--                                                <div style="text-align: right" class="col-4">--}}
{{--                                                    <p>{{number_format($product_variant->price,2)}}</p>--}}

{{--                                                </div>--}}
{{--                                            </div>--}}

{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <hr class="hr_tag">--}}
{{--                                @endforeach--}}






                            </div>

                        </div>



                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4">
                        <div class="card border-light border-0 mt-3  shadow-sm">
                            <div class="card-header  text-dark">
                                <h3>Partner Detail</h3>
                            </div>

                            <div class="card-body bg-white">
                            <label>Partner Name</label>
                                <input type="text" class="form-control mt-1" disabled value="{{$partner->name}}">

                                <label class="mt-2">Partner Email</label>
                                <input type="text" class="form-control mt-1" disabled value="{{$partner->email}}">
                            </div>
                        </div>
                        <div class="card border-light border-0 mt-3  shadow-sm">
                            <div class="card-header  text-dark">
                                <h3>Product Organization</h3>
                            </div>

                            <div class="card-body bg-white">
                            <label>Product Type</label>
                                <input type="text" class="form-control mt-1" name="product_type"  value="{{$product->type}}">

                                <label class="mt-2">Vendor</label>
                                <input type="text" class="form-control mt-1" name="vendor"  value="{{$product->vendor}}">
                            </div>
                        </div>

                        <div class="mt-1">
                            <div class="card border-light border-0 mt-3  shadow-sm">
                                <div class="card-header  text-dark">
                                    <h3>Tags</h3>
                                </div>

                                <div class="card-body bg-white">
                                    <input type="text" data-role="tagsinput" name="tags" value="{{$product->tags}}" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>

        <div class="col-12" style="text-align: right">
            <button type="submit" id="submit_btn" class="btn btn-primary">Save</button>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.js"
            integrity="sha512-VvWznBcyBJK71YKEKDMpZ0pCVxjNuKwApp4zLF3ul+CiflQi6aIJR+aZCP/qWsoFBA28avL5T5HA+RE+zrGQYg=="
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput-angular.min.js"
            integrity="sha512-KT0oYlhnDf0XQfjuCS/QIw4sjTHdkefv8rOJY5HHdNEZ6AmOh1DW/ZdSqpipe+2AEXym5D0khNu95Mtmw9VNKg=="
            crossorigin="anonymous"></script>
    <script>
        var editor1 = new RichTextEditor("#editor1", { editorResizeMode: "none" });
    </script>

    <script>
        var editor2 = new RichTextEditor("#editor2", { editorResizeMode: "none" });
    </script>


    <script>
        $(document).ready(function (){

           $('#submit_btn').click(function (){

             $('#form1').submit();
           });
        });
    </script>
@endsection
