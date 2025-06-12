@include('layouts.header')
@section('title', 'Login')
<!-- Content -->

<div class="authentication-wrapper authentication-cover">
   
    <div class="authentication-inner row m-0">
      <!-- /Left Section -->
      <div class="d-none d-lg-flex col-lg-7 col-xl-7 align-items-center imageDiv bg-white justify-content-center p-5">
        <img
          src="{{ asset('assets/img/login.jpg')}}"
          class="w-100"
          alt="auth-illustration">
      
      </div>
      <!-- /Left Section -->

      <!-- Login -->
      <div
        class="d-flex col-12 col-lg-5 col-xl-5 align-items-center authentication-bg position-relative py-sm-5 px-4 py-4">
        <div class="w-px-400 mx-auto pt-5 pt-lg-0">
          <h4 class="mb-2">Welcome to Atithi Suraksha! 👋</h4>
          <p class="mb-4">Please sign-in to your account</p>

          <form id="loginForm" class="mb-3" action="{{ route('authenticate')}}" method="Post">
            @csrf
            <div class="alert alert-danger alert-dismissible d-none" id="loginError" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div class="form-floating form-floating-outline mb-3">
              <input
                type="text"
                class="form-control"
                id="email"
                name="email"
                placeholder="Enter your email or username"
                autofocus />
              <label for="email">Email or Username</label>
            </div>
            <div class="mb-3">
              <div class="form-password-toggle">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input
                      type="password"
                      id="password"
                      class="form-control"
                      name="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password" />
                    <label for="password">Password</label>
                  </div>
                  <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                </div>
              </div>
            </div>
            <div class="mb-3 float-end">
              <a href="{{ route('forgot-password')}}" class="mb-1">
                <span>Forgot Password?</span>
              </a>
            </div>
            <button type="submit" id="submit" class="btn btn-primary d-grid w-100">Sign in</button>
          </form>
        </div>
      </div>
      <!-- /Login -->
    </div>
  </div>

  <!-- / Content -->

@extends('layouts.scriptLinks')
@section('scripts')
<script src="{{ asset('assets/custom-js/page-auth.js')}}"></script>
@endsection