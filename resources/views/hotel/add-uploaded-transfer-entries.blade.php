@extends('layouts.main')
@section('title', 'Add Uploaded Transfer Entries')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h4 class="card-header d-flex justify-content-between align-items-center">
                Add Uploaded Transfer Entries
                <a href="{{ route('transfer-entries') }}" class="d-none d-sm-inline-block"><button type="button"
                        class="btn btn-primary waves-effect waves-light mx-2">Back</button></a>
            </h4>
            <hr style="margin: 0.25rem">


            <div class="card-body">
                <div class="col-12">
                    @if ($uploads->count() > 0)
                        <form class="form-repeater add-new-record" enctype="multipart/form-data" id="add-form"
                            action="{{ route('store-uploaded-transfer-entry') }}">
                            <table class=" table table-bordered">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Document</th>
                                        <th>Date and Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($uploads as $upload)
                                        <input type="hidden" name="upload_ids[]" value="{{ $upload->id }}">
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @php
                                                    $fileUrl = asset('storage/' . $upload->file_path); // Update path accordingly
                                                    $extension = strtolower(
                                                        pathinfo($upload->document, PATHINFO_EXTENSION),
                                                    );
                                                    $previewType = in_array($extension, [
                                                        'jpg',
                                                        'jpeg',
                                                        'png',
                                                        'gif',
                                                        'bmp',
                                                        'webp',
                                                    ])
                                                        ? 'image'
                                                        : 'pdf';
                                                @endphp

                                                <a href="{{ $fileUrl }}" target="_blank" class="preview-file"
                                                    data-url="{{ $fileUrl }}" data-type="{{ $previewType }}">
                                                    Open File
                                                </a>
                                            </td>
                                            </td>
                                            <td>{{ $upload->created_at->format('d M Y, h:i A') }}</td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="modal-footer mt-2">
                                <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                            </div>
                        </form>
                    @else
                        <p class="text-center">No uploads found.</p>
                    @endif

                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/custom-js/page-add-transfer-entries.js') }}"></script>
@endsection
