@extends('layouts.main')
@section('title', 'Edit Booking')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h4 class="card-header d-flex justify-content-between align-items-center">
                Edit Booking
                <a href="{{ route('bookings') }}" class="d-none d-sm-inline-block"><button type="button"
                        class="btn btn-primary waves-effect waves-light mx-2">Back</button></a>
            </h4>
            <hr style="margin: 0.25rem">


            <div class="card-body">
                <form id="add-edit-form" action=""
                    method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{$booking->id}}">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="text" name="guest_name" id="guest_name"
                                        placeholder="Guest Name" value="{{ $booking->guest_name }}" />
                                    <label for="guest_name">Guest Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="date" name="check_in" id="check_in"
                                        placeholder="YYYY-MM-DD" value="{{ \Carbon\Carbon::parse($booking->check_in)->format('Y-m-d') }}" />
                                    <label for="check_in">Check In</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="date" name="check_out" id="check_out"
                                        placeholder="YYYY-MM-DD" value="{{ \Carbon\Carbon::parse($booking->check_out)->format('Y-m-d') }}" />
                                    <label for="check_out">Check Out</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="text" name="room_number" id="room_number"
                                        placeholder="Room Number" value="{{ $booking->room_number }}" />
                                    <label for="room_number">Room Number</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="email" name="email" id="email"
                                        placeholder="Email" value="{{ $booking->email }}" />
                                    <label for="email">Email</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="number" id="contact_number" name="contact_number"
                                        placeholder="Contact Number"
                                        value="{{ $booking->contact_number }}" />
                                    <label for="contact_number">Contact Number</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="number" id="aadhar_number" name="aadhar_number"
                                        placeholder="Aadhar Number"
                                        value="{{ $booking->aadhar_number }}" />
                                    <label for="aadhar_number">Aadhar Number</label>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <textarea class="form-control" id="address" name="address" placeholder="Address">{{ $booking->address }}</textarea>
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
                                            <option value="{{ $state->id }}" {{ $booking->state_id == $state->id ? 'selected' : '' }}>
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
                                                        {{ $booking->city_id == $city->id ? 'selected' : '' }} >
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
                                        placeholder="Pincode" value="{{ $booking->pincode }}" />
                                    <label for="pincode">Pincode</label>
                                </div>
                            </div>
                        </div>

                            <div class="col-md-6 mb-3">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input type="file" class="form-control document-input"
                                            id="id_proof_path" data-label="ID Proof"
                                            name="id_proof_path" accept="image/*,application/pdf">
                                        <label for="id_proof_path">ID Proof</label>
                                    </div>
                                </div>
                            </div>

                        <!-- Preview section for all selected documents -->
                        <div class="row mt-4" id="all-preview-row"></div>

                        <!-- Preview of already uploaded documents -->
                            <div class="row mt-4" id="uploaded-preview-row">
                                <h5>Uploaded Documents</h5>
                                    <div class="col-md-4 mb-3">
                                        <p class="fw-bold">Aadhar Card Image</p>
                                        @php
                                            $ext = pathinfo($booking->id_proof_path, PATHINFO_EXTENSION);
                                        @endphp
                                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                            <img src="{{ asset('storage/' . $booking->id_proof_path) }}"
                                                alt="Uploaded Document" class="img-fluid" style="max-height: 200px;">
                                        @elseif($ext === 'pdf')
                                            <iframe src="{{ asset('storage/' . $booking->id_proof_path) }}" width="100%"
                                                height="200px" style="border:1px solid #ccc;"></iframe>
                                        @else
                                            <p>No preview available</p>
                                        @endif
                                    </div>
                            </div>


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
