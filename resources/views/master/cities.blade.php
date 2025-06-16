@extends('layouts.main')
@section('title', 'City')
@section('content')

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- DataTable with Buttons -->
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-basic table table-bordered city_table">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>State</th>
                            <th>City</th>
                            <th>Status</th>
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
                <h5 class="offcanvas-title" id="exampleModalLabel">New City</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body flex-grow-1">
                <form class="add-new-record pt-0 row g-3" action="{{ route('add-city') }}" id="addForm">
                    @csrf
                    @method('post')
                    <div class="input-group input-group-merge">
                        <div class="form-floating form-floating-outline">
                            <select class="form-control" name="state_id" id="state_id">
                                <option value="">Select State</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                            <label for="state_id">State</label>
                        </div>
                    </div>
                    <div class="input-group input-group-merge">
                        <div class="form-floating form-floating-outline">
                            <input class="form-control" type="text" name="name" id="name" placeholder="Name" />
                            <label for="name">Name</label>
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

        <div class="offcanvas offcanvas-end" id="editModal">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title">Edit City</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body flex-grow-1">
                <form id="editForm" action="{{ route('update-city') }}" class="pt-0 row g-3">
                    @csrf
                    @method('put')
                    <div class="input-group input-group-merge">
                        <div class="form-floating form-floating-outline">
                            <select class="form-control" name="state_id" id="edit_state_id">
                                <option value="">Select State</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                            <label for="state_id">State</label>
                        </div>
                    </div>
                    <div class="input-group input-group-merge">
                        <div class="form-floating form-floating-outline">
                            <input class="form-control" type="text" name="name" id="edit_name" placeholder="Name" />
                            <label for="name">Name</label>
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
        var changeStatusURl = "{{ route('change-city-status') }}";
        var deleteUrl = "{{ route('delete-city') }}";
        var listUrl = "{{ route('cities') }}";
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-city.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
