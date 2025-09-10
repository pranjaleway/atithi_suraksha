@extends('layouts.main')
@section('title', 'Report')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card" id="section-block">
            <h4 class="card-header">
                Report
            </h4>

            <div class="card-body">
                <form id="reportForm" class="reportForm">
                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <select class="form-select" name="transfer_type" id="transfer_type">
                                        <option value="All">All</option>
                                        <option value="manual">Manual</option>
                                        <option value="uploaded">Uploaded</option>
                                    </select>
                                    <label for="transfer_type">Transfer Type</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control flatpickr-input" name="date_range"
                                        id="flatpickr-range" placeholder="YYYY-MM-DD to YYYY-MM-DD" readonly>
                                    <label for="flatpickr-range">Date Range</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="mt-3">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="reportType" value="list" checked>
                                    List
                                </label>
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="reportType" value="count"> Count
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center my-3">
                        <button type="submit" id="submitBtn" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card lists d-none mt-2">
            <div class="card-datatable table-responsive pt-0">
                <table class="table datatables-basic" id="listing_table">
                    <thead>
                        <tr></tr> {{-- JS will inject the <th> dynamically --}}
                    </thead>
                </table>
            </div>
        </div>


        <div class="card all_count d-none mt-2">
            <div class="card-datatable table-responsive pt-0">
                <table class="table all_count_table" id="all_count_table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Transfer Date</th>
                            <th>Manual Count</th>
                            <th>Uploaded Count</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>
        <div class="card manual_count d-none mt-2">
            <div class="card-datatable table-responsive pt-0">
                <table class="table manual_count_table" id="manual_count_table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Transfer Date</th>
                            <th>Transfer Type</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>

        <div class="card uploaded_count d-none mt-2">
            <div class="card-datatable table-responsive pt-0">
                <table class="table uploaded_count_table" id="uploaded_count_table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Transfer Date</th>
                            <th>Transfer Type</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>

        <div class=" card no-data d-none mt-3">
            <div class="text-center fw-bold my-4">
                No data found
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script>
        var listUrl = "{{ route('hotel-report') }}";
        var viewDetailsUrl = "{{ route('view-booking-details', ':id') }}";
        var uploadedUrl = "{{ asset('storage') }}/";
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-report-hotel.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
