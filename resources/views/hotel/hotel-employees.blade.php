@extends('layouts.main')
@section('title', 'Hotel Employees')
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
                            <th>Employee Name</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Status</th>
                            @if(!$isSuperAdmin)
                            <th>Actions</th>
                            @endif
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
        var changeStatusURl = "{{ route('change-hotel-employee-status') }}";
        var deleteUrl = "{{ route('delete-hotel-employee') }}";
       var listUrl = "{{ route('hotel-employees', base64_encode($id)) }}";

        var isSuperAdmin ={{ $isSuperAdmin ? 'true' : 'false' }};
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-hotel-employee.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
