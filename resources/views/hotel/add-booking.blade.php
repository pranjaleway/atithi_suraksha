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
                    <form class="form-repeater add-new-record" enctype="multipart/form-data" id="add-form" action="{{ route('store-booking') }}">
                        <div data-repeater-list="group-a">
                            <div data-repeater-item class="repeat">
                                <div class="row">
                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" id="guest_name-1-1" name="guest_name" class="form-control"
                                                placeholder="Guest Name" />
                                            <label for="guest_name">Guest Name</label>
                                        </div>
                                        <span class="text-danger" id="guest_nameError_0"></span>
                                    </div>
                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                           <input type="date" min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" class="form-control check_in" placeholder="YYYY-MM-DD" id="check_in-" name="check_in">
                                            <label for="check_in">Check In</label>
                                        </div>
                                        <span class="text-danger" id="check_inError_0"></span>
                                    </div>
                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                           <input type="date" min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" class="form-control " placeholder="YYYY-MM-DD" id="check_out-" name="check_out">
                                            <label for="check_out">Check Out</label>
                                        </div>
                                        <span class="text-danger" id="check_outError_0"></span>
                                    </div>
                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" id="room_number-1-5" name="room_number" class="form-control"
                                                placeholder="Room Number" />
                                            <label for="room_number">Room Number</label>
                                        </div>
                                        <span class="text-danger" id="room_numberError_0"></span>
                                    </div>
                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" id="contact_number-1-6" name="contact_number" class="form-control"
                                                placeholder="Contact Number" />
                                            <label for="contact_number">Contact Number</label>
                                        </div>
                                        <span class="text-danger" id="contact_numberError_0"></span>
                                    </div>
                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                            <input type="number" id="aadhar_number-1-7" name="aadhar_number" class="form-control"
                                                placeholder="Aadhar Number" />
                                            <label for="aadhar_number">Aadhar Number</label>
                                        </div>
                                        <span class="text-danger" id="aadhar_numberError_0"></span>
                                    </div>
                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                            <input type="email" id="email-1-8" name="email" class="form-control"
                                                placeholder="Email" />
                                            <label for="email">Email</label>
                                        </div>
                                    </div>
                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                           <textarea class="form-control" id="address-1-9" name="address" placeholder="Address"></textarea>
                                            <label for="address">Address</label>
                                        </div>
                                        <span class="text-danger" id="addressError_0"></span>
                                    </div>

                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                            <select class="form-select" id="state_id-1-10" name="state_id">
                                                <option value="">Select State</option>
                                                @foreach ($states as $state)
                                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                                @endforeach
                                            </select>                   
                                            <label for="state_id">State</label>
                                        </div>
                                        <span class="text-danger" id="state_idError_0"></span>
                                    </div>
                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                            <select class="form-select" id="city_id-1-11" name="city_id">
                                                <option value="">Select City</option>
                                            </select>                   
                                            <label for="city_id">City</label>
                                        </div>
                                        <span class="text-danger" id="city_idError_0"></span>
                                    </div>
                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" id="pincode-1-12" name="pincode" class="form-control"
                                                placeholder="Pincode" />
                                            <label for="pincode">Pincode</label>
                                        </div>
                                        <span class="text-danger" id="pincodeError_0"></span>
                                    </div>
                                    <div class="mb-3 col-lg-6 col-xl-6 col-12 mb-0">
                                        <div class="form-floating form-floating-outline">
                                            <input type="file" id="id_proof-1-13" name="id_proof_path" class="form-control id-proof-input"
                                                placeholder="Id Proof" accept="image/*,application/pdf" />
                                            <label for="id_proof_path">Id Proof</label>
                                        </div>
                                        <span class="text-danger" id="id_proof_pathError_0"></span>
                                        <div class="preview mt-2"></div>
                                    </div>
                                    
                                    <div class="mb-3 deleteDiv d-none col-lg-12 col-xl-2 col-12 d-flex align-items-center mb-0">
                                        <button class="btn btn-outline-danger" data-repeater-delete type="button">
                                            <i class="mdi mdi-close me-1"></i>
                                            <span class="align-middle">Delete</span>
                                        </button>
                                    </div>
                                </div>
                                <hr />
                            </div>
                        </div>
                        <div class="mb-0">
                            <button class="btn btn-primary" data-repeater-create type="button">
                                <i class="mdi mdi-plus me-1"></i>
                                <span class="align-middle">Add</span>
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
        cityUrl = "{{ route('get-cities') }}";
    </script>
    {{-- <script src="{{ asset('assets/js/forms-extras.js') }}"></script> --}}
    <script src="{{ asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/forms-pickers.js') }}"></script> --}}

    {{-- <script src="{{ asset('assets/custom-js/common.js') }}"></script> --}}
    <script src="{{ asset('assets/custom-js/page-add-booking.js') }}"></script>


@endsection
