@include('layouts.header')
@section('title', 'Hotel Sign Up')
<!-- Content -->

<div class="authentication-wrapper authentication-cover">
    <!-- Logo -->
    <a href="index.html" class="auth-cover-brand d-flex align-items-center gap-2">
        <span class="app-brand-logo demo">
            <span style="color: var(--bs-primary)">
                <svg width="268" height="150" viewBox="0 0 38 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M30.0944 2.22569C29.0511 0.444187 26.7508 -0.172113 24.9566 0.849138C23.1623 1.87039 22.5536 4.14247 23.5969 5.92397L30.5368 17.7743C31.5801 19.5558 33.8804 20.1721 35.6746 19.1509C37.4689 18.1296 38.0776 15.8575 37.0343 14.076L30.0944 2.22569Z"
                        fill="currentColor" />
                    <path
                        d="M30.171 2.22569C29.1277 0.444187 26.8274 -0.172113 25.0332 0.849138C23.2389 1.87039 22.6302 4.14247 23.6735 5.92397L30.6134 17.7743C31.6567 19.5558 33.957 20.1721 35.7512 19.1509C37.5455 18.1296 38.1542 15.8575 37.1109 14.076L30.171 2.22569Z"
                        fill="url(#paint0_linear_2989_100980)" fill-opacity="0.4" />
                    <path
                        d="M22.9676 2.22569C24.0109 0.444187 26.3112 -0.172113 28.1054 0.849138C29.8996 1.87039 30.5084 4.14247 29.4651 5.92397L22.5251 17.7743C21.4818 19.5558 19.1816 20.1721 17.3873 19.1509C15.5931 18.1296 14.9843 15.8575 16.0276 14.076L22.9676 2.22569Z"
                        fill="currentColor" />
                    <path
                        d="M14.9558 2.22569C13.9125 0.444187 11.6122 -0.172113 9.818 0.849138C8.02377 1.87039 7.41502 4.14247 8.45833 5.92397L15.3983 17.7743C16.4416 19.5558 18.7418 20.1721 20.5361 19.1509C22.3303 18.1296 22.9391 15.8575 21.8958 14.076L14.9558 2.22569Z"
                        fill="currentColor" />
                    <path
                        d="M14.9558 2.22569C13.9125 0.444187 11.6122 -0.172113 9.818 0.849138C8.02377 1.87039 7.41502 4.14247 8.45833 5.92397L15.3983 17.7743C16.4416 19.5558 18.7418 20.1721 20.5361 19.1509C22.3303 18.1296 22.9391 15.8575 21.8958 14.076L14.9558 2.22569Z"
                        fill="url(#paint1_linear_2989_100980)" fill-opacity="0.4" />
                    <path
                        d="M7.82901 2.22569C8.87231 0.444187 11.1726 -0.172113 12.9668 0.849138C14.7611 1.87039 15.3698 4.14247 14.3265 5.92397L7.38656 17.7743C6.34325 19.5558 4.04298 20.1721 2.24875 19.1509C0.454514 18.1296 -0.154233 15.8575 0.88907 14.076L7.82901 2.22569Z"
                        fill="currentColor" />
                    <defs>
                        <linearGradient id="paint0_linear_2989_100980" x1="5.36642" y1="0.849138" x2="10.532"
                            y2="24.104" gradientUnits="userSpaceOnUse">
                            <stop offset="0" stop-opacity="1" />
                            <stop offset="1" stop-opacity="0" />
                        </linearGradient>
                        <linearGradient id="paint1_linear_2989_100980" x1="5.19475" y1="0.849139" x2="10.3357"
                            y2="24.1155" gradientUnits="userSpaceOnUse">
                            <stop offset="0" stop-opacity="1" />
                            <stop offset="1" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                </svg>
            </span>
        </span>
        <span class="app-brand-text demo text-heading fw-bold">Atithi Suraksha</span>
    </a>
    <!-- /Logo -->
    <div class="authentication-inner row m-0">
        <!-- Left Text -->
        <div class="d-none d-lg-flex col-lg-4 align-items-center justify-content-center p-5 mt-5 mt-xxl-0">
            <img alt="register-multi-steps-illustration"
                src="../../assets/img/illustrations/auth-register-multi-steps-illustration.png"
                class="h-auto mh-100 w-px-200" />
        </div>
        <!-- /Left Text -->

        <!--  Multi Steps Registration -->
        <div class="d-flex col-lg-8 align-items-center justify-content-center authentication-bg p-5">
            <div class="w-px-700 mt-5 mt-lg-0">
                <div id="multiStepsValidation" class="bs-stepper wizard-numbered">
                    <div class="bs-stepper-header border-bottom-0">
                        <div class="step" data-target="#accountDetailsValidation">
                            <button type="button" class="step-trigger">
                                <span class="bs-stepper-circle"><i class="mdi mdi-check"></i></span>
                                <span class="bs-stepper-label">
                                    <span class="bs-stepper-number">01</span>
                                    <span class="d-flex flex-column gap-1 ms-2">
                                        <span class="bs-stepper-title">Account</span>
                                        <span class="bs-stepper-subtitle">Basic Details</span>
                                    </span>
                                </span>
                            </button>
                        </div>
                        <div class="line"></div>
                        <div class="step" data-target="#personalInfoValidation">
                            <button type="button" class="step-trigger">
                                <span class="bs-stepper-circle"><i class="mdi mdi-check"></i></span>
                                <span class="bs-stepper-label">
                                    <span class="bs-stepper-number">02</span>
                                    <span class="d-flex flex-column gap-1 ms-2">
                                        <span class="bs-stepper-title">Personal</span>
                                        <span class="bs-stepper-subtitle">Enter Information</span>
                                    </span>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="bs-stepper-content">
                        <form id="multiStepsForm" method="POST" onsubmit="return false">
                            <!-- Account Details -->
                            <div id="accountDetailsValidation" class="content">
                                <div class="content-header mb-3">
                                    <h4 class="mb-0">Basic Information</h4>
                                    <small>Enter Your Basic Details</small>
                                </div>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input class="form-control" type="text" name="hotel_name" id="hotel_name"
                                                placeholder="Hotel Name" value="" />
                                            <label for="hotel_name">Hotel Name</label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input class="form-control" type="email" name="email" id="email"
                                                placeholder="Email" value="" />
                                            <label for="email">Email</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input class="form-control" type="number" id="contact_number"
                                                name="contact_number" placeholder="Contact Number" value="" />
                                            <label for="contact_number">Hotel Contact Number</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input class="form-control" type="text" id="license_number"
                                                name="license_number" placeholder="Hotel License Number"
                                                value="{{ isset($hotels) ? $hotels->license_number : '' }}" />
                                            <label for="license_number">Hotel License Number</label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 form-password-toggle">
                                        <div class="input-group input-group-merge">
                                            <div class="form-floating form-floating-outline">
                                                <input type="password" id="password" class="form-control"
                                                    name="password"
                                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                    aria-describedby="password" />
                                                <label for="password">Password</label>
                                            </div>
                                            <span class="input-group-text cursor-pointer"><i
                                                    class="mdi mdi-eye-off-outline"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 form-password-toggle">
                                        <div class="input-group input-group-merge">
                                            <div class="form-floating form-floating-outline">
                                                <input type="password" id="password_confirmation"
                                                    class="form-control" name="password_confirmation"
                                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                    aria-describedby="password" />
                                                <label for="password_confirmation">Confirm Password</label>
                                            </div>
                                            <span class="input-group-text cursor-pointer"><i
                                                    class="mdi mdi-eye-off-outline"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <textarea class="form-control" id="address" name="address" placeholder="Address">{{ isset($hotels) ? $hotels->address : '' }}</textarea>
                                            <label for="address">Address</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
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
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <select class="form-select" id="city_id" name="city_id">
                                                <option selected value="" disabled>Select City</option>
                                            </select>
                                            <label for="city_id">City Name</label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input class="form-control" type="number" id="pincode" name="pincode"
                                                placeholder="Pincode" value="" />
                                            <label for="pincode">Pincode</label>
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex justify-content-between">
                                        <button class="btn btn-secondary btn-prev" disabled>
                                            <i class="mdi mdi-arrow-left me-sm-1 me-0"></i>
                                            <span class="align-middle d-sm-inline-block d-none">Previous</span>
                                        </button>
                                        <button class="btn btn-primary btn-next">
                                            <span
                                                class="align-middle d-sm-inline-block d-none me-sm-1 me-0">Next</span>
                                            <i class="mdi mdi-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Personal Info -->
                            <div id="personalInfoValidation" class="content">
                                <div class="content-header mb-3">
                                    <h4 class="mb-0">Personal Information</h4>
                                    <small>Enter Your Personal Information</small>
                                </div>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input class="form-control" type="text" name="owner_name"
                                                id="owner_name" placeholder="Hotel Owner Name" value="" />
                                            <label for="owner_name">Hotel Owner Name</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input class="form-control" type="number" id="owner_contact_number"
                                                name="owner_contact_number" placeholder="Owner Contact Number"
                                                value="" />
                                            <label for="owner_contact_number">Owner Contact Number</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input class="form-control" type="number" id="aadhar_number"
                                                name="aadhar_number" placeholder="Aadhar Number" value="" />
                                            <label for="aadhar_number">Aadhar Number</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating form-floating-outline">
                                            <input class="form-control" type="number" id="pan_number"
                                                name="pan_number" placeholder="Pan Number" value="" />
                                            <label for="pan_number">Pan Number</label>
                                        </div>
                                    </div>
                                    @foreach ($documents as $document)
                                        <div class="col-sm-6">
                                            <div class="input-group input-group-merge">
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
                                        </div>
                                    @endforeach

                                    <!-- Preview section for all selected documents -->
                                    <div class="row mt-4" id="all-preview-row"></div>

                                    <div class="col-12 d-flex justify-content-between">
                                        <button class="btn btn-secondary btn-prev">
                                            <i class="mdi mdi-arrow-left me-sm-1 me-0"></i>
                                            <span class="align-middle d-sm-inline-block d-none">Previous</span>
                                        </button>
                                        <button type="submit"
                                            class="btn btn-primary btn-next btn-submit">Submit</button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- / Multi Steps Registration -->
    </div>
</div>

<!-- / Content -->

@extends('layouts.scriptLinks')
@section('scripts')
    <script>
        cityUrl = "{{ route('get-cities') }}";
        signupUrl = "{{ route('post-hotel-signup') }}";
    </script>
    <script src="{{ asset('assets/custom-js/common.js') }}"></script>
    <script src="{{ asset('assets/custom-js/page-hotel-signup.js') }}"></script>
@endsection
