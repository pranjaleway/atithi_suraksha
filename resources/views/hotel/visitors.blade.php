@extends('layouts.main')
@section('title', 'Visitors')
@section('content')

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- DataTable with Buttons -->
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-basic table table-bordered">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Visitor Name</th>
                            <th>Aadhar Number</th>
                            <th>Contact Number</th>
                            <th>Entry Time</th>
                            <th>Address</th>
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
        var deleteUrl = "{{ route('delete-visitor') }}";
        var listUrl = "{{ route('visitors', base64_encode($id)) }}";
        var addVisitorUrl = "{{ route('add-visitor', base64_encode($id)) }}";
        var viewDetailsUrl = "{{ route('view-visitor-details', ':id') }}";
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-visitor.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
