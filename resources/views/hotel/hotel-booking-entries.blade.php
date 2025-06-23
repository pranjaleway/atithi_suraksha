@extends('layouts.main')
@section('title', 'Uploaded Entries')
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
                           <th>Hotel Name</th>
                           <th>Uploaded Entries</th>
                           <th>Bookings</th>
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
        var listUrl = "{{ route('hotel-booking-entries') }}";
        var uploadedEntriesUrl= "{{ route('uploaded-entries', ':id') }}";
        var bookingUrl= "{{ route('bookings', ':id') }}";
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-hotel-booking-entries.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
