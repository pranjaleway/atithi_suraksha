@extends('layouts.main')
@section('title', 'Menu')
@section('content')

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
<!-- DataTable with Buttons -->
<div class="card">
    <div class="card-datatable table-responsive pt-0">
        <table class="datatables-basic table table-bordered menu_table">
            <thead>
                <tr>
                    <th></th> <!-- Drag column -->
                    <th>S.No.</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Sub Menu</th>
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
        <h5 class="offcanvas-title" id="exampleModalLabel">New Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form class="add-new-record pt-0 row g-3" action="{{route('add-menu')}}" id="menuForm">
            @csrf
            @method('post')
            <input type="hidden" name="parent_id" value="" id="parent_id">
            <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                    <input class="form-control" type="text" name="name" id="name"
                        placeholder="Name" />
                    <label for="name">Name</label>
                </div>
            </div>
            <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                    <input class="form-control" type="text" name="icon" id="icon"
                        placeholder="Icon" />
                    <label for="icon">Icon</label>
                </div>
            </div>
            <div class="input-group input-group-merge">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="visible_at_web" id="visible_at_web">
                    <label class="form-check-label" for="visible_at_web">Visible at Web</label>
                </div>
            </div>
            <div class="input-group input-group-merge">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="visible_at_app" id="visible_at_app">
                    <label class="form-check-label" for="visible_at_app">Visible At App</label>
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

<div class="offcanvas offcanvas-end" id="menuEdit">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">Edit Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form id="menuEditForm" action="{{route('update-menu')}}" class="pt-0 row g-3">
            @csrf
            @method('put')
            <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                    <input class="form-control" type="text" name="name" id="edit_name"
                        placeholder="Name" />
                    <label for="name">Name</label>
                </div>   
            </div>
            <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                    <input class="form-control" type="text" name="icon" id="edit_icon"
                        placeholder="Icon" />
                    <label for="icon">Icon</label>
                </div>   
            </div>
            <div class="input-group input-group-merge">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="edit_visible_at_web" id="edit_visible_at_web">
                    <label class="form-check-label" for="visible_at_web">Visible at Web</label>
                </div>
            </div>
            <div class="input-group input-group-merge">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="edit_visible_at_app" id="edit_visible_at_app">
                    <label class="form-check-label" for="visible_at_app">Visible At App</label>
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
    var changeStatusURl = "{{ route('change-menu-status') }}";
    var deleteUrl = "{{ route('delete-menu') }}";
    var menuUrl = "{{ route('menus') }}";
    var parent_id = $("#parent_id").val() || null;

</script>
<script src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>
<script src="{{ asset('assets/custom-js/tables-datatables-menu.js') }}"></script>
<script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection