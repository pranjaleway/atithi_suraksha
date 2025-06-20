@extends('layouts.main')
@section('title', 'Employee Details')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card" id="section-block">
            <h4 class="card-header d-flex justify-content-between align-items-center">
                <span>Employee Details</span>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('hotel-employees') }}" class="d-none d-sm-inline-block">
                        <button type="button" class="btn btn-primary waves-effect waves-light mx-1">Back</button>
                    </a>
                </div>
            </h4>
            <hr style="margin: 0.25rem">

            <div class="card-header p-0">
                <div class="nav-align-top">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-general-info" aria-controls="navs-top-general-info"
                                aria-selected="true">
                                General Details
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-document" aria-controls="navs-top-document" aria-selected="false">
                                Document
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content p-0">
                    <div class="tab-pane fade show active" id="navs-top-general-info" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th scope="row">Name</th>
                                        <td>{{ $employee->employee_name }}</td>
                                        <th scope="row">Contact Number</th>
                                        <td>{{ $employee->contact_number }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Email</th>
                                        <td>{{ $employee->email }}</td>
                                        <th scope="row">Aadhar Number</th>
                                        <td>{{ $employee->aadhar_number }}</td>
                                    </tr>
                                     <tr>
                                        <th scope="row">Pan Number</th>
                                        <td>{{ $employee->pan_number }}</td>
                                        <th scope="row">employee Status</th>
                                        <td>{{ $employee->status == 1 ? 'Active' : 'Inactive' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Address</th>
                                        <td colspan="3">{{ $employee->address }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">City</th>
                                        <td>{{ $employee->city->name }}</td>
                                        <th>State</th>
                                        <td>{{ $employee->state->name }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Pincode</th>
                                        <td>{{ $employee->pincode }}</td>
                                    </tr>
                                   

                                </tbody>

                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="navs-top-document" role="tabpanel">
                        @if ($employee->employeeDocuments->count())
                            <div class="row mt-4" id="uploaded-preview-row">
                                @foreach ($employee->employeeDocuments as $doc)
                                    <div class="col-md-4 mb-3">
                                        <p class="fw-bold">{{ $doc->document->name ?? 'Document' }}</p>
                                        @php
                                            $ext = pathinfo($doc->document_path, PATHINFO_EXTENSION);
                                        @endphp
                                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                            <img src="{{ asset('storage/' . $doc->document_path) }}"
                                                alt="Uploaded Document" class="img-fluid" style="max-height: 200px;">
                                        @elseif($ext === 'pdf')
                                            <iframe src="{{ asset('storage/' . $doc->document_path) }}" width="100%"
                                                height="200px" style="border:1px solid #ccc;"></iframe>
                                        @else
                                            <p>No preview available</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No documents available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endsection
