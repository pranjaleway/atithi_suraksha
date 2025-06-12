@extends('layouts.main')
@section('title', 'User Access')
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
                    <th>Menu</th>
                    <th>View</th>
                    <th>Add</th>
                    <th>Edit</th>
                    <th>Delete</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!--/ DataTable with Buttons -->

<!-- Permission Modal  -->
<div class="modal fade" id="addPermissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLabel3">Add Access</h4>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <form action="{{ route('add-user-access') }}" id="addPermissionForm" method="POST">
        <div class="modal-body">
            <div class="row">
              @if ($menus->isNotEmpty())
              @foreach ($menus as $menu)
                  <div class="col-6 mt-2">
                      <div class="form-check">
                          <input class="form-check-input  menu-checkbox" name="menu_id[]" type="checkbox" value="{{ $menu->id }}" id="menu_id_{{$menu->id}}"  data-id="{{ $menu->id }}"  data-parent-id="{{ $menu->parent_id ?? '' }}">
                          <label class="form-check-label" for="menu_id_{{$menu->id}}">{{ $menu->name }}</label>
                      </div>
                  </div>
              @endforeach
          @else
              <div class="col-12 mt-2 text-center">
                  <p class="text-muted">No menu found</p>
              </div>
          @endif
          
            </div>
            
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Close
          </button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
        </form>
      </div>
    </div>
  </div>

<!--/ Permission Modal  -->

</div>

<!--/ Edit Modal -->
@endsection
@section('scripts')
<script>
    var deleteUrl = "{{ route('delete-user-access') }}";
</script>
<script src="{{ asset('assets/custom-js/tables-datatables-user-access.js') }}"></script>
<script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection