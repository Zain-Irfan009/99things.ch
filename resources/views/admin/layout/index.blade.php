<!doctype html>
<html lang="en">
@include('admin.layout.head')

<style>
    .alert{
        padding: 4px;
        padding-top: 10px;
    }
    .table-vcenter{
        font-size: 14px !important;
    }

</style>
</head>
<body >
<div class="page">

    @include('admin.layout.sidebar')
    @include('admin.layout.navbar')
    <div class="page-wrapper">
        <div class="container-xl">
            <!-- Page title -->

        </div>
        <div class="page-body">
            <div class="container-xl">
                <div class="row row-deck row-cards">
                    <div class="alert alert-important alert-success alert-dismissible " style="display: none" role="alert" id="alertSuccess">
                        <div class="d-flex">
                            <div>
                                <!-- Download SVG icon from http://tabler-icons.io/i/check -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l5 5l10 -10"></path></svg>
                            </div>
                            <div id="alertSuccessText">

                            </div>
                        </div>
                        {{--            <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>--}}
                    </div>
                    @yield('content')

                </div>
            </div>
        </div>
{{--        @include('admin.layout.footer')--}}
    </div>
</div>

<!-- Libs JS -->
<script src="{{asset('dist/libs/apexcharts/dist/apexcharts.min.js')}}" defer></script>
<script src="{{asset('dist/libs/jsvectormap/dist/js/jsvectormap.min.js')}}" defer></script>
<script src="{{asset('dist/libs/jsvectormap/dist/maps/world.js')}}" defer></script>
<script src="{{asset('dist/libs/jsvectormap/dist/maps/world-merc.js')}}" defer></script>
<!-- Tabler Core -->
<script src="{{asset('dist/js/tabler.min.js')}}" defer></script>
<script src="{{asset('dist/js/demo.min.js')}}" defer></script>



<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script>
    @if(Session::has('success'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : false,
            "positionClass": "toast-top-right",

        }
    toastr.success("{{ session('success') }}");
    @endif

        @if(Session::has('error'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : false,
            "positionClass": "toast-top-right",
        }
    toastr.error("{{ session('error') }}");
    @endif

        @if(Session::has('info'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : false,
            "positionClass": "toast-top-right",
        }
    toastr.info("{{ session('info') }}");
    @endif

        @if(Session::has('warning'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : false,
            "positionClass": "toast-top-right",
        }
    toastr.warning("{{ session('warning') }}");
    @endif
</script>

</body>
</html>
