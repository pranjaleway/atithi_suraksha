@extends('layouts.main')
@section('title', 'Activity Log')
@section('content')
 <!-- Content -->
 <div class="container-xxl flex-grow-1 container-p-y">
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table table-bordered activityLogTable">
                <thead>
                    <tr>
                        <th></th>
                        <th>S.No.</th>
                        <th>Activity</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    
 </div>
 @endsection
 @section('scripts')
 <script>
     var deleteUrl = "{{ route('delete-activity-log') }}";
 </script>

 <script src="{{ asset('assets/custom-js/tables-datatables-activity-log.js') }}"></script>

 @endsection