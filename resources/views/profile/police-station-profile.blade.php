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
                        <form id="profile-details-form" action="{{ route('update-police-station-profile') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $policeStation->id }}">
                            <div class="tab-content pb-1">
                                <div id="profile-details" class="tab-pane fade show active">
                                    <div class="row">
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="police_station_name" name="police_station_name"
                                                    placeholder="Police Station Name" aria-label="Police Station Name" value="{{ Auth::User()->name }}" />
                                                <label for="police_station_name">Police Station Name</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="officer_in_charge" name="officer_in_charge"
                                                    placeholder="Officer In Charge" aria-label="Officer In Charge" value="{{ $policeStation->officer_in_charge }}" />
                                                <label for="officer_in_charge">Officer In Charge</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <input type="email" class="form-control" id="email" name="email"
                                                    placeholder="Email" aria-label="Email" value="{{ Auth::User()->email }}" />
                                                <label for="email">Email</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <input type="number" class="form-control" id="contact_number" name="contact_number"
                                                    placeholder="Contact Number" aria-label="Contact Number" value="{{ Auth::User()->phone }}" />
                                                <label for="contact_number">Contact Number</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" id="address" name="address"
                                                    placeholder="Address" aria-label="Address" value="{{ $policeStation->address }}" />
                                                <label for="address">Address</label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                            <div class="form-floating form-floating-outline">
                                                 <select class="form-select" id="state_id" name="state_id">
                                                    <option selected value="" disabled>Select State</option>
                                                    @foreach ($states as $state)
                                                        <option value="{{ $state->id }}"
                                                            @isset($policeStation)
                                                                {{ $policeStation->state_id == $state->id ? 'selected' : '' }}
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
                                                    @isset($policeStation)
                                                        @foreach ($cities as $city)
                                                            <option value="{{ $city->id }}"
                                                                @isset($policeStation)
                                                                    {{ $policeStation->city_id == $city->id ? 'selected' : '' }}
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
                                                    placeholder="Pincode" aria-label="Pincode" value="{{ $policeStation->pincode }}" />
                                                <label for="pincode">Pincode</label>
                                            </div>
                                        </div>
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
