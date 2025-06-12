@include('layouts.header')
@section('title', 'Reset Password')

<!-- Content -->

<div class="authentication-wrapper authentication-cover">
    <div class="authentication-inner row m-0">
      <!-- /Left Section -->
      <div class="d-none d-lg-flex bg-white col-lg-7 imageDiv col-xl-7 align-items-center justify-content-center p-5">
        <img
          src="{{ asset('assets/img/login.jpg')}}"
          class="w-100"
          alt="auth-illustration">
      
      </div>
      <!-- /Left Section -->

      <!-- Reset Password -->
      <div
        class="d-flex col-12 col-lg-5 col-xl-5 align-items-center authentication-bg position-relative py-sm-5 px-4 py-4">
        <div class="w-px-400 mx-auto pt-5 pt-lg-0">
          <h4 class="mb-2">Reset Password 🔒</h4>
          <p class="mb-4">Your new password must be different from previously used passwords</p>
          <form id="resetPasswordForm" class="mb-3" action="{{ route('post-reset-password')}}" method="POST">
            @csrf
            <div class="alert alert-danger alert-dismissible d-none" id="resetPasswordError" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <div class="mb-3 form-password-toggle">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input
                    type="password"
                    id="password"
                    class="form-control"
                    name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <label for="password">New Password</label>
                </div>
                <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
              </div>
            </div>
            <div class="mb-3 form-password-toggle">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input
                    type="password"
                    id="confirm-password"
                    class="form-control"
                    name="confirm-password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <label for="confirm-password">Confirm Password</label>
                </div>
                <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
              </div>
            </div>
            <button class="btn btn-primary d-grid w-100 mb-3">Set new password</button>
            <div class="text-center">
              <a href="{{ route('login')}}" class="d-flex align-items-center justify-content-center">
                <i class="mdi mdi-chevron-left scaleX-n1-rtl mdi-24px"></i>
                Back to login
              </a>
            </div>
          </form>
        </div>
      </div>
      <!-- /Reset Password -->
    </div>
  </div>

  <!-- / Content -->

@extends('layouts.scriptLinks')
@section('scripts')
 <script src="{{ asset('assets/custom-js/page-reset-password.js')}}"></script> 
@endsection