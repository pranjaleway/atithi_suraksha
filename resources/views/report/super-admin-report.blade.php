@extends('layouts.main')
@section('title', 'Report')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
    </div>
@endsection
@section('scripts')
    <script>
        var hotelsByPoliceStationURL = "{{ route('hotels-by-police-station') }}";
        var listUrl = "{{ route('sp-office-report') }}";
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-report-sp-office.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
