@extends('layouts.main')
@section('title', 'Hotels')
@section('content')

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- DataTable with Buttons -->
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-basic table table-bordered sp_office_table">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Hotel Name</th>
                            <th>Owner Name</th>
                            <th>Hotel Contact Number</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>
        <!--/ DataTable with Buttons -->

       
    </div>
@endsection
@section('scripts')
    <script>
        var changeStatusURl = "{{ route('change-hotel-status') }}";
        var deleteUrl = "{{ route('delete-hotel') }}";
        var listUrl = "{{ route('hotels') }}";
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-hotel.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
