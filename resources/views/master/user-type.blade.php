@extends('layouts.main')
@section('title', 'User Type')
@section('content')

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
<!-- DataTable with Buttons -->
<div class="card">
    <div class="card-datatable table-responsive pt-0">
        <table class="datatables-basic table table-bordered user_type_table">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>User Type</th>
                    <th>Status</th>
                    @if(Auth::user()->role == 0 || Auth::user()->role == 1)
                    <th>Access</th>
                    @endif
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!--/ DataTable with Buttons -->

<!-- Modal to add new record -->
<div class="offcanvas offcanvas-end" id="add-new-record">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="exampleModalLabel">New User Type</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form class="add-new-record pt-0 row g-3" action="{{route('add-user-type')}}" id="userTypeForm">
            @csrf
            @method('post')
            <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                    <input class="form-control" type="text" name="user_type" id="user_type"
                        placeholder="User Type" />
                    <label for="user_type">User Type</label>
                </div>
            </div>
            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Submit</button>
                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>
    </div>
</div>
<!--/ Modal to add new record -->

<!-- Edit Modal -->

<div class="offcanvas offcanvas-end" id="userTypeEdit">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">Edit User Type</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form id="userTypeEditForm" action="{{route('update-user-type')}}" class="pt-0 row g-3">
            @csrf
            @method('put')
            <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                    <input class="form-control" type="text" name="user_type" id="edit_user_type"
                        placeholder="User Type" />
                    <label for="user_type">User Type</label>
                </div>   
            </div>
            <input type="hidden" name="id" id="edit_id">
            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Submit</button>
                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>
    </div>
</div>

</div>

<!--/ Edit Modal -->
@endsection
@section('scripts')
<script>
    var changeStatusURl = "{{ route('change-user-type-status') }}";
    var deleteUrl = "{{ route('delete-user-type') }}";
    var userRole = @json(Auth::user()->role);
</script>
<script src="{{ asset('assets/custom-js/tables-datatables-user-type.js') }}"></script>
<script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection