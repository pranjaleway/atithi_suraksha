@extends('layouts.main')
@section('title', 'Room Master')
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
                            <th>Room Number</th>
                            <th>Room Type</th>
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
                <h5 class="offcanvas-title" id="exampleModalLabel">New Room Master</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body flex-grow-1">
                <form class="add-new-record pt-0 row g-3" action="{{ route('add-room-master') }}" id="addForm">
                    @csrf
                    @method('post')
                    <div class="input-group input-group-merge">
                        <div class="form-floating form-floating-outline">
                            <input class="form-control" type="text" name="room_number" id="room_number" placeholder="Room Number" />
                            <label for="room_number">Room Number</label>
                        </div>
                    </div>
                    <div class="input-group input-group-merge">
                        <div class="form-floating form-floating-outline">
                            <select class="form-control" name="room_type" id="room_type">
                                <option value="">Select Room Type</option>
                                <option value="AC">AC</option>
                                <option value="NON-AC">NON AC</option>
                            </select>
                            <label for="room_type">Room Type</label>
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
                <h5 class="offcanvas-title">Edit Room Master</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body flex-grow-1">
                <form id="editForm" action="{{ route('update-room-master') }}" class="pt-0 row g-3">
                    @csrf
                    @method('put')
                    <div class="input-group input-group-merge">
                        <div class="form-floating form-floating-outline">
                            <input class="form-control" type="text" name="room_number" id="edit_room_number" placeholder="Room Number" />
                            <label for="room_number">Room Number</label>
                        </div>
                    </div>
                    <div class="input-group input-group-merge">
                        <div class="form-floating form-floating-outline">
                            <select class="form-control" name="room_type" id="edit_room_type">
                                <option value="">Select Room Type</option>
                                <option value="AC">AC</option>
                                <option value="NON-AC">NON AC</option>
                            </select>
                            <label for="room_type">Room Type</label>
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
        var changeStatusURl = "{{ route('change-room-master-status') }}";
        var deleteUrl = "{{ route('delete-room-master') }}";
        var listUrl = "{{ route('room-master') }}";
        var editUrl = "{{ route('edit-room-master') }}";
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-room-master.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
