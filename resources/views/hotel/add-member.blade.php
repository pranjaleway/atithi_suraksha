@extends('layouts.main')
@section('title', 'Add Member')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h4 class="card-header d-flex justify-content-between align-items-center">
                Add Member
                <a href="{{ route('bookings') }}" class="d-none d-sm-inline-block"><button type="button"
                        class="btn btn-primary waves-effect waves-light mx-2">Back</button></a>
            </h4>
            <hr style="margin: 0.25rem">


            <div class="card-body">
                <form id="add-edit-form" action="{{ route('store-member') }}"
                    method="POST">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{$booking->id}}">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="text" name="guest_name" id="guest_name"
                                        placeholder="Guest Name" value="" />
                                    <label for="guest_name">Guest Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="date" name="check_in" id="check_in"
                                        placeholder="YYYY-MM-DD" value="" min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" />
                                    <label for="check_in">Check In</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="date" name="check_out" id="check_out"
                                        placeholder="YYYY-MM-DD" value="" min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" />
                                    <label for="check_out">Check Out</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="text" name="room_number" id="room_number"
                                        placeholder="Room Number" value="" />
                                    <label for="room_number">Room Number</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="email" name="email" id="email"
                                        placeholder="Email" value="" />
                                    <label for="email">Email</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="number" id="contact_number" name="contact_number"
                                        placeholder="Contact Number"
                                        value="" />
                                    <label for="contact_number">Contact Number</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="number" id="aadhar_number" name="aadhar_number"
                                        placeholder="Aadhar Number"
                                        value="" />
                                    <label for="aadhar_number">Aadhar Number</label>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <textarea class="form-control" id="address" name="address" placeholder="Address"></textarea>
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
                                            <option value="{{ $state->id }}">
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
                                                <option value="{{ $city->id }}">
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
                                        placeholder="Pincode" value="" />
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
    <script src="{{ asset('assets/custom-js/page-add-member.js') }}"></script>


@endsection
