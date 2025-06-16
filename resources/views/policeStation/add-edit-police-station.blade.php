@extends('layouts.main')
@section('title', isset($policeStations) ? 'Edit Police Station' : 'Add Police Station')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h4 class="card-header d-flex justify-content-between align-items-center">
                {{ isset($policeStations) ? 'Edit Police Station' : 'Add Police Station' }}
                <a href="{{ route('police-stations') }}" class="d-none d-sm-inline-block"><button type="button"
                        class="btn btn-primary waves-effect waves-light mx-2">Back</button></a>
            </h4>
            <hr style="margin: 0.25rem">


            <div class="card-body">
                <form id="add-edit-form"
                    action="{{ isset($policeStations) ? route('update-police-station') : route('store-police-station') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ isset($policeStations) ? $policeStations->id : '' }}">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                   <input class="form-control" type="text" name="police_station_name" id="police_station_name"
                                        placeholder="Police Station Name"
                                        value="{{ isset($policeStations) ? $policeStations->police_station_name : '' }}" />
                                    <label for="police_station_name">Police Station Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="text" name="officer_in_charge" id="officer_in_charge"
                                        placeholder="Office In Charge"
                                       value="{{ isset($policeStations) ? $policeStations->officer_in_charge : '' }}" />
                                    <label for="officer_in_charge">Office In Charge</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="email" name="email" id="email"
                                        placeholder="Email" value="{{ isset($policeStations) ? $policeStations->email : '' }}" />
                                    <label for="email">Email</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input class="form-control" type="number" id="contact_number" name="contact_number"
                                        placeholder="Contact Number"
                                        value="{{ isset($policeStations) ? $policeStations->contact_number : '' }}" />
                                    <label for="contact_number">Contact Number</label>
                                </div>
                            </div>
                        </div>
                        @if (!isset($policeStations))
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
                                    <textarea class="form-control" id="address" name="address" placeholder="Address">{{ isset($policeStations) ? $policeStations->address : '' }}</textarea>
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
                                               @isset($policeStations)
                                                   {{ $policeStations->state_id == $state->id ? 'selected' : '' }}
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
                                        @isset($policeStations)
                                            @foreach ($cities as $city)
                                                <option value="{{ $city->id }}"
                                                    @isset($policeStations)
                                                        {{ $policeStations->city_id == $city->id ? 'selected' : '' }} 
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
                                        placeholder="Pincode"
                                        value="{{ isset($policeStations) ? $policeStations->pincode : '' }}" />
                                    <label for="pincode">Pincode</label>
                                </div>
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
    <script src="{{ asset('assets/custom-js/page-add-edit-police-station.js') }}"></script>


@endsection
