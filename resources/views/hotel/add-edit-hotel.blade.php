@extends('layouts.main')
@section('title', isset($hotels) ? 'Edit Hotel' : 'Add Hotel')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h4 class="card-header d-flex justify-content-between align-items-center">
                {{ isset($hotels) ? 'Edit Hotel' : 'Add Hotel' }}
                <a href="{{ route('hotels') }}" class="d-none d-sm-inline-block"><button type="button"
                        class="btn btn-primary waves-effect waves-light mx-2">Back</button></a>
            </h4>
            <hr style="margin: 0.25rem">


            <div class="card-body">
                <form id="add-edit-form" action="{{ isset($hotels) ? route('update-hotel') : route('store-hotel') }}"
                    method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ isset($hotels) ? $hotels->id : '' }}">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="text" name="hotel_name" id="hotel_name"
                                        placeholder="Hotel Name" value="{{ isset($hotels) ? $hotels->hotel_name : '' }}" />
                                    <label for="hotel_name">Hotel Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="text" name="owner_name" id="owner_name"
                                        placeholder="Hotel Owner Name"
                                        value="{{ isset($hotels) ? $hotels->owner_name : '' }}" />
                                    <label for="owner_name">Hotel Owner Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="email" name="email" id="email"
                                        placeholder="Email" value="{{ isset($hotels) ? $hotels->email : '' }}" />
                                    <label for="email">Email</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="number" id="contact_number" name="contact_number"
                                        placeholder="Contact Number"
                                        value="{{ isset($hotels) ? $hotels->contact_number : '' }}" />
                                    <label for="contact_number">Hotel Contact Number</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="number" id="owner_contact_number"
                                        name="owner_contact_number" placeholder="Owner Contact Number"
                                        value="{{ isset($hotels) ? $hotels->owner_contact_number : '' }}" />
                                    <label for="owner_contact_number">Owner Contact Number</label>
                                </div>
                            </div>
                        </div>
                        @if (!isset($hotels))
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
                                        value="{{ isset($hotels) ? $hotels->aadhar_number : '' }}" />
                                    <label for="aadhar_number">Aadhar Number</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="text" id="pan_number" name="pan_number"
                                        placeholder="Pan Number"
                                        value="{{ isset($hotels) ? $hotels->pan_number : '' }}" />
                                    <label for="pan_number">Pan Number</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="text" id="license_number" name="license_number"
                                        placeholder="Hotel License Number"
                                        value="{{ isset($hotels) ? $hotels->license_number : '' }}" />
                                    <label for="license_number">Hotel License Number</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <select class="form-select" id="police_station_id" name="police_station_id">
                                        <option selected value="" disabled>Select Police Station</option>
                                        @foreach ($policeStations as $policeStation)
                                            <option value="{{ $policeStation->id }}"
                                                @isset($hotels)
                                                   {{ $hotels->police_station_id == $policeStation->id ? 'selected' : '' }}
                                               @endisset>
                                                {{ $policeStation->police_station_name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="police_station_id">Police Station</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <textarea class="form-control" id="address" name="address" placeholder="Address">{{ isset($hotels) ? $hotels->address : '' }}</textarea>
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
                                                @isset($hotels)
                                                   {{ $hotels->state_id == $state->id ? 'selected' : '' }}
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
                                        @isset($hotels)
                                            @foreach ($cities as $city)
                                                <option value="{{ $city->id }}"
                                                    @isset($hotels)
                                                        {{ $hotels->city_id == $city->id ? 'selected' : '' }} 
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
                                        placeholder="Pincode" value="{{ isset($hotels) ? $hotels->pincode : '' }}" />
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
                        @if (isset($hotels) && $hotels->ownerDocuments->count())
                            <div class="row mt-4" id="uploaded-preview-row">
                                <h5>Uploaded Documents</h5>
                                @foreach ($hotels->ownerDocuments as $doc)
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
    <script src="{{ asset('assets/custom-js/page-add-edit-hotel.js') }}"></script>


@endsection
