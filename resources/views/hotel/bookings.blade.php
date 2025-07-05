@extends('layouts.main')
@section('title', 'Bookings')
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
                            <th>Contact Number</th>
                            <th>Room Number</th>
                            <th>Booking Date</th>
                            @if (Auth::user()->user_type_id == 4 || Auth::user()->user_type_id == 5)
                                <th>Members</th>
                                <th>Visitors</th>
                            @endif
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
        var deleteUrl = "{{ route('delete-booking') }}";
        var listUrl = "{{ route('bookings', [$hotel_id ?? '', $date ?? '']) }}";
        var membersUrl = "{{ route('members', ':id') }}";
        var visitorsUrl = "{{ route('visitors', ':id') }}";
        var viewDetailsUrl = "{{ route('view-booking-details', ':id') }}";
        var addBookingUrl = "{{ route('add-booking') }}";
        var userRole = @json(Auth::user()->user_type_id);
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-booking.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
