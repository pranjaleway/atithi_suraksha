@extends('layouts.main')
@section('title', 'Members')
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
                            <th>Guest Name</th>
                            <th>Aadhar Number</th>
                            <th>Contact Number</th>
                            <th>Room Number</th>
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
        var deleteUrl = "{{ route('delete-member') }}";
        var listUrl = "{{ route('members', base64_encode($id)) }}";
        var addMemberUrl = "{{ route('add-member', base64_encode($id)) }}";
        var viewDetailsUrl = "{{ route('view-booking-details', ':id') }}";
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-members.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
