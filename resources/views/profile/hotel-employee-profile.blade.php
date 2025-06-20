@extends('layouts.main')
@section('title', 'Profile')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-4">
            <div class="card-header p-0">
                <div class="nav-align-top">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-general-info" aria-controls="navs-top-general-info"
                                aria-selected="true">
                                Profile
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-vehicle-details" aria-controls="navs-top-vehicle-details"
                                aria-selected="false">
                                Change Password
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-1">
                <div class="tab-content p-0">
                    <div class="tab-pane fade show active" id="navs-top-general-info" role="tabpanel">
                        <form id="profile-details-form" action="{{ route('update-hotel-employee-profile') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $employee->id }}">
                            <div class="tab-content pb-1">
                                <div id="profile-details" class="tab-pane fade show active">
                                    <div class="row">
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="employee_name" name="employee_name"
                                                    placeholder="employee Name" aria-label="employee Name"
                                                    value="{{ $employee->employee_name }}" />
                                                <label for="employee_name">Employee Name</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <input type="email" class="form-control" id="email" name="email"
                                                    placeholder="Email" aria-label="Email"
                                                    value="{{ Auth::User()->email }}" />
                                                <label for="email">Email</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <input type="number" class="form-control" id="contact_number"
                                                    name="contact_number" placeholder="Contact Number"
                                                    aria-label="Contact Number" value="{{ $employee->contact_number }}" />
                                                <label for="contact_number">Contact Number</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <input class="form-control" type="number" id="aadhar_number"
                                                    name="aadhar_number" placeholder="Aadhar Number"
                                                    value="{{ $employee->aadhar_number }}" />
                                                <label for="aadhar_number">Aadhar Number</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <input class="form-control" type="text" id="pan_number" name="pan_number"
                                                    placeholder="Pan Number" value="{{ $employee->pan_number }}" />
                                                <label for="pan_number">Pan Number</label>
                                            </div>
                                        </div>

                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="address" name="address"
                                                    placeholder="Address" aria-label="Address"
                                                    value="{{ $employee->address }}" />
                                                <label for="address">Address</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <select class="form-select" id="state_id" name="state_id">
                                                    <option selected value="" disabled>Select State</option>
                                                    @foreach ($states as $state)
                                                        <option value="{{ $state->id }}"
                                                            @isset($employee)
                                                                {{ $employee->state_id == $state->id ? 'selected' : '' }}
                                                            @endisset>
                                                            {{ $state->name }}</option>
                                                    @endforeach
                                                </select>
                                                <label for="state_id">State Name</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <select class="form-select" id="city_id" name="city_id">
                                                    <option selected value="" disabled>Select City</option>
                                                    @isset($employee)
                                                        @foreach ($cities as $city)
                                                            <option value="{{ $city->id }}"
                                                                @isset($employee)
                                                                    {{ $employee->city_id == $city->id ? 'selected' : '' }}
                                                                @endisset>
                                                                {{ $city->name }}</option>
                                                        @endforeach
                                                    @endisset
                                                </select>
                                                <label for="city_id">City Name</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <input type="number" class="form-control" id="pincode" name="pincode"
                                                    placeholder="Pincode" aria-label="Pincode"
                                                    value="{{ $employee->pincode }}" />
                                                <label for="pincode">Pincode</label>
                                            </div>
                                        </div>
                                        @foreach ($documents as $document)
                                            <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                                <div class="form-floating form-floating-outline">
                                                    <input type="file" class="form-control document-input"
                                                        id="document_{{ $document->id }}"
                                                        data-label="{{ $document->name }}"
                                                        name="document[{{ $document->id }}]"
                                                        accept="image/*,application/pdf">
                                                    <label
                                                        for="document_{{ $document->id }}">{{ $document->name }}</label>
                                                </div>
                                            </div>
                                        @endforeach

                                        <!-- Preview section for all selected documents -->
                                        <div class="row mt-4" id="all-preview-row"></div>
                                        <!-- Preview of already uploaded documents -->
                                        @if ($employee->employeeDocuments->count())
                                            <div class="row mt-4" id="uploaded-preview-row">
                                                <h5>Uploaded Documents</h5>
                                                @foreach ($employee->employeeDocuments as $doc)
                                                    <div class="col-md-4 mb-3">
                                                        <p class="fw-bold">{{ $doc->document->name ?? 'Document' }}</p>
                                                        @php
                                                            $ext = pathinfo($doc->document_path, PATHINFO_EXTENSION);
                                                        @endphp
                                                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                            <img src="{{ asset('storage/' . $doc->document_path) }}"
                                                                alt="Uploaded Document" class="img-fluid"
                                                                style="max-height: 200px;">
                                                        @elseif($ext === 'pdf')
                                                            <iframe src="{{ asset('storage/' . $doc->document_path) }}"
                                                                width="100%" height="200px"
                                                                style="border:1px solid #ccc;"></iframe>
                                                        @else
                                                            <p>No preview available</p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer py-0">
                                <button type="submit" class="btn btn-primary me-sm-3 m-1">Update</button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="navs-top-vehicle-details" role="tabpanel">
                        @include('profile.partials.change-password')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        cityUrl = "{{ route('get-cities') }}";
    </script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
    <script src="{{ asset('assets/custom-js/page-profile.js') }}"></script>
@endsection
