@extends('layouts.main')
@section('title', isset($employee) ? 'Edit Employee' : 'Add Employee')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h4 class="card-header d-flex justify-content-between align-items-center">
                {{ isset($employee) ? 'Edit Employee' : 'Add Employee' }}
                <a href="{{ route('hotel-employees') }}" class="d-none d-sm-inline-block"><button type="button"
                        class="btn btn-primary waves-effect waves-light mx-2">Back</button></a>
            </h4>
            <hr style="margin: 0.25rem">


            <div class="card-body">
                <form id="add-edit-form" action="{{ isset($employee) ? route('update-hotel-employee') : route('store-hotel-employee') }}"
                    method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ isset($employee) ? $employee->id : '' }}">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="text" name="employee_name" id="employee_name"
                                        placeholder="Employee Name" value="{{ isset($employee) ? $employee->employee_name : '' }}" />
                                    <label for="employee_name">Employee Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="email" name="email" id="email"
                                        placeholder="Email" value="{{ isset($employee) ? $employee->email : '' }}" />
                                    <label for="email">Email</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="number" id="contact_number" name="contact_number"
                                        placeholder="Contact Number"
                                        value="{{ isset($employee) ? $employee->contact_number : '' }}" />
                                    <label for="contact_number">Contact Number</label>
                                </div>
                            </div>
                        </div>
                        @if (!isset($employee))
                            <div class="form-password-toggle col-md-6 mb-2">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input type="password" id="password" class="form-control" name="password"
                                            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                            aria-describedby="password" />
                                        <label for="password">Password</label>
                                    </div>
                                    <span class="input-group-text cursor-pointer"><i
                                            class="mdi mdi-eye-off-outline"></i></span>
                                </div>
                            </div>

                            <div class="form-password-toggle col-md-6 mb-2">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input type="password" id="password_confirmation" class="form-control"
                                            name="password_confirmation"
                                            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                            aria-describedby="password" />
                                        <label for="password_confirmation">Confirm Password</label>
                                    </div>
                                    <span class="input-group-text cursor-pointer"><i
                                            class="mdi mdi-eye-off-outline"></i></span>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="number" id="aadhar_number" name="aadhar_number"
                                        placeholder="Aadhar Number"
                                        value="{{ isset($employee) ? $employee->aadhar_number : '' }}" />
                                    <label for="aadhar_number">Aadhar Number</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="text" id="pan_number" name="pan_number"
                                        placeholder="Pan Number"
                                        value="{{ isset($employee) ? $employee->pan_number : '' }}" />
                                    <label for="pan_number">Pan Number</label>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <textarea class="form-control" id="address" name="address" placeholder="Address">{{ isset($employee) ? $employee->address : '' }}</textarea>
                                    <label for="address">Address</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
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
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
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
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="number" id="pincode" name="pincode"
                                        placeholder="Pincode" value="{{ isset($employee) ? $employee->pincode : '' }}" />
                                    <label for="pincode">Pincode</label>
                                </div>
                            </div>
                        </div>

                        @foreach ($documents as $document)
                            <div class="col-md-6 mb-3">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input type="file" class="form-control document-input"
                                            id="document_{{ $document->id }}" data-label="{{ $document->name }}"
                                            name="document[{{ $document->id }}]" accept="image/*,application/pdf">
                                        <label for="document_{{ $document->id }}">{{ $document->name }}</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Preview section for all selected documents -->
                        <div class="row mt-4" id="all-preview-row"></div>

                        <!-- Preview of already uploaded documents -->
                        @if (isset($employee) && $employee->employeeDocuments->count())
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
                        @endif


                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary me-2">Save</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        cityUrl = "{{ route('get-cities') }}";
    </script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
    <script src="{{ asset('assets/custom-js/page-add-edit-hotel-employee.js') }}"></script>


@endsection
