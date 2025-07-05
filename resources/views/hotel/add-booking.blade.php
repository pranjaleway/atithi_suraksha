@extends('layouts.main')
@section('title', 'Add Booking')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h4 class="card-header d-flex justify-content-between align-items-center">
                Add Booking
                <a href="{{ route('bookings') }}" class="d-none d-sm-inline-block"><button type="button"
                        class="btn btn-primary waves-effect waves-light mx-2">Back</button></a>
            </h4>
            <hr style="margin: 0.25rem">


            <div class="card-body">
                <div class="col-12">
                    <form class="form-repeater add-new-record" enctype="multipart/form-data" id="add-form"
                        action="{{ route('store-booking') }}">
                        <div data-repeater-list="group-a">
                            <div data-repeater-item class="repeat">
                                <div class="row">
                                    <!-- Guest Name -->
                                    <div class="mb-3 col-lg-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" name="guest_name" class="form-control"
                                                placeholder="Guest Name" />
                                            <label>Guest Full Name</label>
                                        </div>
                                        <span class="text-danger guest_name-error"></span>
                                    </div>

                                    <!-- Contact Number -->
                                    <div class="mb-3 col-lg-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" name="contact_number" class="form-control"
                                                placeholder="Contact Number" />
                                            <label>Contact Number</label>
                                        </div>
                                        <span class="text-danger contact_number-error"></span>
                                    </div>
                                    <!-- Hide in repeat rows -->
                                    <!-- Email -->
                                    <div class="mb-3 col-lg-6 common-fields">
                                        <div class="form-floating form-floating-outline">
                                            <input type="email" name="email" class="form-control" placeholder="Email" />
                                            <label>Email</label>
                                        </div>
                                    </div>
                                    <!-- End Hide in repeat rows -->

                                    <!-- Age -->
                                    <div class="mb-3 col-lg-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="number" name="age" placeholder="Age" class="form-control" />
                                            <label>Age</label>
                                        </div>
                                        <span class="text-danger dob-error"></span>
                                    </div>

                                    <!-- Gender -->
                                    <div class="mb-3 col-lg-6">
                                        <div class="form-floating form-floating-outline">
                                            <select class="form-select" name="gender" id="gender">
                                                <option selected value="" disabled>Select Gender</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                                <option value="other">Other</option>
                                            </select>
                                            <label>Gender</label>
                                        </div>
                                        <span class="text-danger gender-error"></span>
                                    </div>

                                    <!-- Aadhar Number -->
                                    <div class="mb-3 col-lg-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="number" name="aadhar_number" class="form-control"
                                                placeholder="Aadhar Number" />
                                            <label>Aadhar Number</label>
                                        </div>
                                        <span class="text-danger aadhar_number-error"></span>
                                    </div>

                                    <!-- Hide in repeat rows -->

                                    <!-- No of Guest -->
                                    <div class="mb-3 col-lg-6 common-fields">
                                        <div class="form-floating form-floating-outline">
                                            <input type="number" min="1" name="no_of_guest" id="no_of_guest"
                                                class="form-control" placeholder="Number of Guest" />
                                            <label>Number of Guest</label>
                                        </div>
                                        <span class="text-danger no_of_guest-error"></span>

                                        <div class="row mt-3 count-fields d-none">
                                            <div class="col-md-4">
                                                <div class="form-floating form-floating-outline">
                                                    <input type="text" name="no_of_male" class="form-control"
                                                        placeholder="Number of Male" />
                                                    <label for="no_of_male">Number of Male</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-floating form-floating-outline">
                                                    <input type="text" name="no_of_female" class="form-control"
                                                        placeholder="Number of Female" />
                                                    <label for="no_of_female">Number of Female</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-floating form-floating-outline">
                                                    <input type="text" name="no_of_children" class="form-control"
                                                        placeholder="Number of Children" />
                                                    <label for="no_of_children">Number of Children</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Check-in -->
                                    <div class="mb-3 col-lg-6 common-fields">
                                        <div class="form-floating form-floating-outline">
                                            <input type="datetime-local" name="check_in" class="form-control check_in"
                                                min="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}" />
                                            <label>Check In</label>
                                        </div>
                                        <span class="text-danger check_in-error"></span>
                                    </div>

                                    <!-- Check-out -->
                                    <div class="mb-3 col-lg-6 common-fields">
                                        <div class="form-floating form-floating-outline">
                                            <input type="datetime-local" name="check_out" class="form-control"
                                                min="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}" />
                                            <label>Check Out</label>
                                        </div>
                                        <span class="text-danger check_out-error"></span>
                                    </div>

                                    <!-- Room Number -->
                                    <div class="mb-3 col-lg-6 common-fields">
                                        <div class="form-floating form-floating-outline">
                                            <select multiple class="form-select room_number_id" name="room_number_id"
                                                id="room_number_id">

                                            </select>
                                            <label>Room Number</label>
                                        </div>
                                        <span class="text-danger room_number_id-error"></span>
                                    </div>



                                    <!-- Same as above address checkbox (hidden initially) -->
                                    <div class="mb-3 col-lg-6 same-address-wrapper d-none">
                                        <div class="form-check mt-2">
                                            <input type="checkbox" checked name="same_address" value="1"
                                                class="form-check-input same-address-checkbox" />
                                            <label class="form-check-label">Same as above address</label>
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div class="mb-3 col-lg-6 address-fields">
                                        <div class="form-floating form-floating-outline">
                                            <textarea class="form-control" name="address" placeholder="Address"></textarea>
                                            <label>Address</label>
                                        </div>
                                        <span class="text-danger address-error"></span>
                                    </div>

                                    <!-- State -->
                                    <div class="mb-3 col-lg-6 address-fields">
                                        <div class="form-floating form-floating-outline">
                                            <select class="form-select" name="state_id" id="state_id-">
                                                <option value="">Select State</option>
                                                @foreach ($states as $state)
                                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                                @endforeach
                                            </select>
                                            <label>State</label>
                                        </div>
                                        <span class="text-danger state_id-error"></span>
                                    </div>

                                    <!-- City -->
                                    <div class="mb-3 col-lg-6 address-fields">
                                        <div class="form-floating form-floating-outline">
                                            <select class="form-select" name="city_id" id="city_id-">
                                                <option value="">Select City</option>
                                            </select>
                                            <label>City</label>
                                        </div>
                                        <span class="text-danger city_id-error"></span>
                                    </div>

                                    <!-- Pincode -->
                                    <div class="mb-3 col-lg-6 address-fields">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" name="pincode" class="form-control"
                                                placeholder="Pincode" />
                                            <label>Pincode</label>
                                        </div>
                                        <span class="text-danger pincode-error"></span>
                                    </div>

                                    <!-- ID Proof -->
                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                            <input type="file" id="id_proof-1-14" name="id_proof_path"
                                                class="form-control id-proof-input" placeholder="Id Proof"
                                                accept="image/*,application/pdf" />
                                            <label for="id_proof_path">Id Proof</label>
                                        </div>
                                        <span class="text-danger" id="id_proof_pathError_0"></span>
                                        <div class="preview mt-2"></div>
                                    </div>



                                    <div class="mb-3 d-none showOtherGuestsDiv">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="showOtherGuests">
                                            <label class="form-check-label" for="showOtherGuests">
                                                Do you want to add other member details?
                                            </label>
                                        </div>
                                    </div>



                                    <!-- Delete Button -->
                                    <div class="mb-3 deleteDiv d-none col-lg-12">
                                        <button class="btn btn-outline-danger" data-repeater-delete type="button">
                                            <i class="mdi mdi-close me-1"></i> Delete
                                        </button>
                                    </div>

                                </div>
                                <hr />
                            </div>
                        </div>

                        <div class="mb-0">
                            <button class="btn btn-primary d-none" data-repeater-create type="button">
                                <i class="mdi mdi-plus me-1"></i> Add
                            </button>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        </div>
                    </form>


                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        var cityUrl = "{{ route('get-cities') }}";
        var getRoomUrl = "{{ route('get-room-number') }}";
    </script>
    {{-- <script src="{{ asset('assets/js/forms-extras.js') }}"></script> --}}
    <script src="{{ asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/forms-pickers.js') }}"></script> --}}

    {{-- <script src="{{ asset('assets/custom-js/common.js') }}"></script> --}}
    <script src="{{ asset('assets/custom-js/page-add-booking.js') }}"></script>


@endsection
