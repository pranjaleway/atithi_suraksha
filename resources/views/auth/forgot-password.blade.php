@include('layouts.header')
@section('title', 'Forgot Password')

<!-- Content -->

<div class="authentication-wrapper authentication-cover">
    <div class="authentication-inner row m-0">
      <!-- /Left Section -->
      <div class="d-none bg-white d-lg-flex col-lg-7 col-xl-7 imageDiv align-items-center justify-content-center p-5">
        <img
          src="{{ asset('assets/img/login.jpg')}}"
          class="w-100"
          alt="auth-illustration">
      
      </div>
      <!-- /Left Section -->

      <!-- Forgot Password -->
      <div class="d-flex col-12 col-lg-5 col-xl-5 align-items-center authentication-bg p-sm-5 p-4">
        <div class="w-px-400 mx-auto">
          <h4 class="mb-2">Forgot Password? 🔒</h4>
          <p class="mb-4">Enter your email and we'll send you instructions to reset your password</p>
          <form id="forgotPasswordForm" class="mb-3" action="{{ route('post-forgot-password')}}" method="POST">
            @csrf
            <div class="alert alert-danger alert-dismissible d-none" id="forgotPasswordError" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <div class="form-floating form-floating-outline mb-3">
              <input
                type="text"
                class="form-control"
                id="email"
                name="email"
                placeholder="Enter your email"
                autofocus />
              <label for="email">Email</label>
            </div>
            <button type="submit" class="btn btn-primary d-grid w-100">Send Reset Link</button>
          </form>
          <div class="text-center">
            <a href="{{ route('login')}}" class="d-flex align-items-center justify-content-center">
              <i class="mdi mdi-chevron-left scaleX-n1-rtl mdi-24px"></i>
              Back to login
            </a>
          </div>
        </div>
      </div>
      <!-- /Forgot Password -->
    </div>
  </div>

  <!-- / Content -->

@extends('layouts.scriptLinks')
@section('scripts')
<script src="{{ asset('assets/custom-js/page-forgot-password.js')}}"></script>
@endsection