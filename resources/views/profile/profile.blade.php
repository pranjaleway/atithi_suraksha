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
                    <form id="profile-details-form" action="{{ route('update-profile')}}" method="POST">
                        @csrf
                        <div class="tab-content pb-1">
                            <div id="profile-details" class="tab-pane fade show active">
                                <div class="row">
                                    <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="Name" aria-label="Name"
                                                value="{{ Auth::User()->name }}" />
                                            <label for="name">Name</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-6 fv-plugins-icon-container">
                                        <div class="form-floating form-floating-outline">
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="Email" aria-label="Email"
                                                value="{{ Auth::User()->email }}" />
                                            <label for="email">Email</label>
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
                    <form id="changePasswordForm" action="{{ route('post-change-password')}}" method="POST">
                        @csrf
                        <div class="tab-content pb-1">
                          <div id="change-password" class="tab-pane fade show active">
                        <div class="row">
                          <div class="mb-3 col-md-6 form-password-toggle">
                            <div class="input-group input-group-merge">
                              <div class="form-floating form-floating-outline">
                                <input
                                  class="form-control"
                                  type="password"
                                  name="currentPassword"
                                  id="currentPassword"
                                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                <label for="currentPassword">Current Password</label>
                              </div>
                              <span class="input-group-text cursor-pointer"
                                ><i class="mdi mdi-eye-off-outline"></i
                              ></span>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="mb-3 col-md-6 form-password-toggle">
                            <div class="input-group input-group-merge">
                              <div class="form-floating form-floating-outline">
                                <input
                                  class="form-control"
                                  type="password"
                                  id="newPassword"
                                  name="newPassword"
                                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                <label for="newPassword">New Password</label>
                              </div>
                              <span class="input-group-text cursor-pointer"
                                ><i class="mdi mdi-eye-off-outline"></i
                              ></span>
                            </div>
                          </div>
                        </div>
                        <div class="row g-3 mb-4">
                          <div class="col-md-6 form-password-toggle">
                            <div class="input-group input-group-merge">
                              <div class="form-floating form-floating-outline">
                                <input
                                  class="form-control"
                                  type="password"
                                  name="confirmPassword"
                                  id="confirmPassword"
                                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                <label for="confirmPassword">Confirm New Password</label>
                              </div>
                              <span class="input-group-text cursor-pointer"
                                ><i class="mdi mdi-eye-off-outline"></i
                              ></span>
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
            </div>
        </div>
    </div>
</div>
        @endsection

        @section('scripts')
            <script src="{{ asset('assets/custom-js/page-profile.js') }}"></script>
        @endsection
