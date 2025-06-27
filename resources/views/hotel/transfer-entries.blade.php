@extends('layouts.main')
@section('title', 'Transfer Entries')
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
                            <th>Date</th>
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
        var deleteUrl = "{{ route('delete-uploaded-entry') }}";
        var listUrl = "{{ route('transfer-entries') }}";
        var manualTransferEntriesUrl = "{{ route('transfer-manual-entries')}}";
        var uploadedTransferEntriesUrl = "{{ route('transfer-uploaded-entries')}}";
        var uploadedUrl = "{{ asset('storage') }}/";
        var uploadedEntriesUrl= "{{ route('uploaded-entries', ['id' => '__ID__', 'date' => '__DATE__']) }}";
        var bookingUrl = "{{ route('bookings', ['id' => '__ID__', 'date' => '__DATE__']) }}";
        var userRole = @json(Auth::user()->user_type_id);
        var hotels = @json($hotels);        
    </script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/custom-js/tables-datatables-transfer-entries.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
