@extends('admin.layout.index')

@section('content')


    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">

                <h1 class="page-title">
                    Logs
                </h1>
            </div>

        </div>





        <div class="page-body">
            <div class="">
                <div class="row row-cards">
                    <div class="col-12">
                        <div class="card">
                            <div class="table-responsive">
                                @if (count($logs) > 0)
                                    <table
                                        class="table table-vcenter table-mobile-md card-table">
                                        <thead>
                                        <tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Start Time</th>
                                            <th scope="col">End Time</th>
                                            <th scope="col">Total Products</th>
                                            <th scope="col">Products Pushed</th>
                                            <th scope="col">Products Left</th>
                                            <th scope="col">Status</th>

                                        </tr>
                                        </thead>
                                        <tbody>

                                        @foreach($logs as $log)
                                            <tr>
                                                <td>{{$log->name}}</td>
                                                <td>{{ $log->date }}</td>
                                                <td>{{ $log->start_time }}</td>
                                                <td>{{ $log->end_time }}</td>
                                                <td>{{ $log->total_products }}</td>
                                                <td>{{ $log->products_pushed }}</td>
                                                <td>{{ $log->products_left }}</td>
                                                <td>
                                                    <span class="badge @if($log->status=='Complete') bg-success @elseif($log->status=='In-Progress') bg-warning @elseif($log->status=='Pending') bg-primary @else ($log->status=='Failed') bg-danger @endif">{{$log->status}}</span>
                                                </td>


                                            </tr>

                                        @endforeach

                                        </tbody>
                                    </table>
                                @else
                                    <h3 class="mx-3 my-3">No Logs Found</h3>
                                @endif
                                <div class="pagination">
                                    {{ $logs->appends(\Illuminate\Support\Facades\Request::except('page'))->links("pagination::bootstrap-4") }}
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>


@endsection
