@extends('layouts.main')
@section('title', 'Uploaded Entries')
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
                            <th>Document</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>
        <!--/ DataTable with Buttons -->

        <!-- Upload Modal  -->
        <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel3">Upload</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('store-uploaded-entry') }}" id="add-form" enctype="multipart/form-data"
                        method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input type="file" class="form-control document-input" id="file_path"
                                            data-label="Entries" name="file_path[]" accept="image/*,application/pdf"
                                            multiple>
                                        <label for="file_path">Upload</label>
                                    </div>
                                </div>
                            </div>
                            <!-- Preview section for all selected documents -->
                            <div class="row mt-4" id="all-preview-row"></div>

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

        <!--/ Upload Modal  -->


    </div>
@endsection
@section('scripts')
    <script>
        var deleteUrl = "{{ route('delete-uploaded-entry') }}";
        var listUrl = "{{ route('uploaded-entries', $hotel_id ?? '') }}";
    </script>
    <script src="{{ asset('assets/custom-js/tables-datatables-uploaded-entries.js') }}"></script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
@endsection
