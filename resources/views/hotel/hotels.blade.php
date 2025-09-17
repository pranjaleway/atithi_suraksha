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
                            <th>Owner Contact Number</th>
                            <th>Address</th>
                            <th>Employee</th>
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
        var employeeUrl = "{{ route('hotel-employees', ':id') }}";
        var addUrl = "{{ route('add-hotel') }}";
        var editUrl = "{{ route('edit-hotel', ':id') }}";
        var viewDetailsUrl = "{{ route('view-hotel-details', ':id') }}";
        var resetPasswordUrl = "{{ route('reset-hotel-password') }}";
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-hotel.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
