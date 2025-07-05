@extends('layouts.main')
@section('title', 'Dashboard')
@section('content')
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Card Border Shadow -->
        <div class="row">
            <div class="col-sm-6 col-lg-4 mb-4">
                <a href="{{ route('police-stations') }}">
                    <div class="card card-border-shadow-warning h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2 pb-1">
                                <div class="avatar me-2">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="mdi mdi-police-station mdi-20px"></i></span>
                                </div>
                                <h4 class="ms-1 mb-0 display-6">{{ $totalPoliceStation }}</h4>
                            </div>
                            <p class="mb-0 text-heading">Police Stations</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-4 mb-4">
                <a href="{{ route('transfer-entries') }}">
                    <div class="card card-border-shadow-success h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2 pb-1">
                                <div class="avatar me-2">
                                    <span class="avatar-initial rounded bg-label-success"><i
                                            class="mdi mdi-file-swap-outline mdi-20px"></i></span>
                                </div>
                                <h4 class="ms-1 mb-0 display-6">{{ $totalTransferredBookings }}</h4>
                            </div>
                            <p class="mb-0 text-heading">Total Transferred Bookings</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-4 mb-4">
                <a href="{{ route('transfer-entries') }}">
                    <div class="card card-border-shadow-info h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2 pb-1">
                                <div class="avatar me-2">
                                    <span class="avatar-initial rounded bg-label-info"><i
                                            class="mdi mdi-file-arrow-left-right-outline mdi-20px"></i></span>
                                </div>
                                <h4 class="ms-1 mb-0 display-6">{{ $todayTransferredBookings }}</h4>
                            </div>
                            <p class="mb-0 text-heading">Today Transferred Bookings</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">
            <!--  Transfer Booking overview -->
             <div class="col-xxl-6 mb-4 order-3 order-xxl-0">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Transfer Booking Overview</h5>
                        <div class="d-flex align-items-center">
                            <div class="form-floating form-floating-outline me-2">
                                <input type="text" class="form-control flatpickr-input" id="flatpickr-range"
                                    placeholder="YYYY-MM-DD to YYYY-MM-DD" readonly>
                                <label for="flatpickr-range">Date Range</label>
                            </div>
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="hotel-filter">
                                    <option value="">All Hotels</option>
                                    @foreach ($hotels as $hotel)
                                        <option value="{{ $hotel->id }}">{{ $hotel->hotel_name }}</option>
                                    @endforeach
                                </select>
                                <label for="hotel-filter">Hotel</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="transfer-booking-overview"></div>
                    </div>
                </div>
            </div>
            <!--/ Transfer Booking overview -->
        </div>

    </div>
    <!-- / Content -->

@endsection
@section('scripts')
    <script>
        const filteredGraphUrl = "{{ route('get-filtered-data') }}";
        const bookingOverviewData = @json($graphData);
    </script>
    <script src="{{ asset('assets/custom-js/super-admin-dashboard.js') }}"></script>
@endsection
