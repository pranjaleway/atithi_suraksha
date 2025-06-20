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