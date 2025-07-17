@extends('layouts.main')
@section('title', 'Notifications')
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
                            <th>User Name</th>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Image</th>
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
                <h5 class="offcanvas-title" id="exampleModalLabel">New Notification</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body flex-grow-1">
                <form class="add-new-record pt-0 row g-3" action="{{ route('store-notification') }}" id="addForm">
                    @csrf
                    @method('post')
                    <div class="input-group input-group-merge">
                        <div class="form-floating form-floating-outline">
                            <input class="form-control" type="text" name="title" id="title" placeholder="Title" />
                            <label for="title">Title</label>
                        </div>
                    </div>
                    <div class="input-group input-group-merge">
                        <div class="form-floating form-floating-outline">
                            <textarea class="form-control" type="text" name="message" id="message" placeholder="Message" rows="3"></textarea>
                            <label for="message">Message</label>
                        </div>
                    </div>
                    <div class="input-group input-group-merge">
                        <div class="form-floating form-floating-outline">
                            <input type="file" class="form-control document-input" id="image"
                                data-label="Image" name="image" accept="image/*">
                            <label for="image">Upload</label>
                        </div>
                    </div>
                     <!-- Preview section for all selected documents -->
                    <div class="row mt-4" id="all-preview-row"></div>

                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Submit</button>
                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <!--/ Modal to add new record -->

      

    </div>

@endsection
@section('scripts')
    <script>
        var deleteUrl = "{{ route('delete-notification') }}";
        var listUrl = "{{ route('notifications') }}";
        var fileUrl = "{{ asset('storage') }}/";
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-notification.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
