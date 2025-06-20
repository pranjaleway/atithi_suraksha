'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const formChangePass = document.querySelector('#changePasswordForm');

  if (formChangePass) {
    const fv = FormValidation.formValidation(formChangePass, {
        fields: {
          currentPassword: {
            validators: {
              notEmpty: {
                message: 'Please enter current password'
              },
              stringLength: {
                min: 6,
                message: 'Password must be more than 8 characters'
              }
            }
          },
          newPassword: {
            validators: {
              notEmpty: {
                message: 'Please enter new password'
              },
              stringLength: {
                min: 6,
                message: 'Password must be more than 8 characters'
              }
            }
          },
          confirmPassword: {
            validators: {
              notEmpty: {
                message: 'Please confirm new password'
              },
              identical: {
                compare: function () {
                  return formChangePass.querySelector('[name="newPassword"]').value;
                },
                message: 'The password and its confirm are not the same'
              },
              stringLength: {
                min: 6,
                message: 'Password must be more than 8 characters'
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: '.col-md-6'
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          // Submit the form when all fields are valid
          // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: instance => {
          instance.on('plugins.message.placed', function (e) {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      });

    // Prevent normal form submission
   

    fv.on("core.form.valid", function () { 
      var currentPassword = $("#currentPassword").val();
      var newPassword = $("#newPassword").val();
      var confirmPassword = $("#confirmPassword").val();
      var url = formChangePass.getAttribute('action');
      var submitButton = $("button[type='submit']");
      
      toggleButtonLoadingState(submitButton, true);
  
      $.ajax({
          url: url,
          type: 'POST',
          data: {
              currentPassword: currentPassword,
              newPassword: newPassword,
              confirmPassword: confirmPassword
          },
          headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
          success: function (data) {            
              if (data.success) {
                  fv.resetForm();
                  $(".is-invalid").removeClass("is-invalid"); // Remove validation error classes
                  $(".invalid-feedback").empty(); // Remove previous error messages
                  toastr.success(data.message, 'Success');
              } else {
                  toastr.error(data.message, 'Error');
              }
          },
          error: function (xhr, status, error) {
              if (xhr.status === 422) { 
                  let errors = xhr.responseJSON.errors;
  
                  // Remove existing validation messages before adding new ones
                  $(".is-invalid").removeClass("is-invalid");
                  $(".invalid-feedback").empty();
  
                  $.each(errors, function (key, value) {
                      let inputField = $("#" + key);
                      
                      if (inputField.length) {
                          inputField.addClass('is-invalid');
  
                          // Check if the input is inside `.input-group`
                          let errorMessage = '<div class="invalid-feedback">' + value[0] + '</div>';
                          if (inputField.closest(".input-group").length) {
                              inputField.closest(".input-group").after(errorMessage);
                          } else if (inputField.closest(".form-floating").length) {
                              inputField.closest(".form-floating").append(errorMessage);
                          } else {
                              inputField.after(errorMessage);
                          }
                      }
                  });
              }
          },
          complete: function () {
              toggleButtonLoadingState(submitButton, false);
          }
      });
  });
  
    
    

  }
});

document.addEventListener('DOMContentLoaded', function () {
    const formUpdateProfile = document.querySelector('#profile-details-form');
  
    if (formUpdateProfile) {
      const fvp = FormValidation.formValidation(formUpdateProfile, {
          fields: {
            name: {
              validators: {
                notEmpty: {
                  message: 'Please enter name'
                }
              }
              },
              email: {
                validators: {
                  notEmpty: {
                    message: 'Please enter email'
                  },
                  emailAddress: {
                    message: 'Please enter a valid email address'
                  }
                }
              },
              office_name: {
                validators: {
                  notEmpty: {
                    message: 'Please enter office name'
                  }
                }
              },
              police_station_name: {
                validators: {
                  notEmpty: {
                    message: 'Please enter police station name'
                  }
                }
              },
              hotel_name: {
                validators: {
                  notEmpty: {
                    message: 'Please enter hotel name'
                  }
                }
              },
              owner_name: {
                validators: {
                  notEmpty: {
                    message: 'Please enter hotel owner name'
                  }
                }
              },
              owner_contact_number: {
                validators: {
                  notEmpty: {
                    message: 'Please enter hotel owner contact number'
                  },
                  stringLength: {
                    min: 10,
                    max: 10,
                    message: 'Mobile number must be 10 digits'
                  },
                  digits: {
                    message: 'Please enter a valid mobile number'
                  }
                }
              },
              contact_number: {
                validators: {
                  notEmpty: {
                    message: 'Please enter contact number'
                  },
                  stringLength: {
                    min: 10,
                    max: 10,
                    message: 'Mobile number must be 10 digits'
                  },
                  digits: {
                    message: 'Please enter a valid mobile number'
                  }
                }
              },
              aadhar_number: {
                validators: {
                  notEmpty: {
                    message: 'Please enter aadhar number'
                  },
                  stringLength: {
                    min: 12,
                    max: 12,
                    message: 'Aadhar number must be 12 digits'
                  },
                  digits: {
                    message: 'Please enter a valid aadhar number'
                  }
                }
              },
              pan_number: {
                validators: {
                  notEmpty: {
                    message: 'Please enter pan number'
                  },
                  stringLength: {
                    min: 10,
                    max: 10,
                    message: 'Pan number must be 10 digits'
                  },
                  digits: {
                    message: 'Please enter a valid pan number'
                  }
                }
              },
              license_number: {
                validators: {
                  notEmpty: {
                    message: 'Please enter license number'
                  }
                }
              },
              address: {
                validators: {
                  notEmpty: {
                    message: 'Please enter address'
                  }
                }
              },
              state_id: {
                validators: {
                  notEmpty: {
                    message: 'Please select state'
                  }
                }
              },
              city_id: {
                validators: {
                  notEmpty: {
                    message: 'Please select city'
                  }
                }
              },
              pincode: {
                validators: {
                  notEmpty: {
                    message: 'Please enter pincode'
                  },
                  stringLength: {
                    min: 6,
                    max: 6,
                    message: 'Pincode must be 6 digits'
                  },
                  digits: {
                    message: 'Please enter a valid pincode'
                  }
                }
              }
          },
          plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
              eleValidClass: '',
              rowSelector: '.col-md-6'
            }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            // Submit the form when all fields are valid
            // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
            autoFocus: new FormValidation.plugins.AutoFocus()
          },
          init: instance => {
            instance.on('plugins.message.placed', function (e) {
              if (e.element.parentElement.classList.contains('input-group')) {
                e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
              }
            });
          }
        });
  
      // Prevent normal form submission
     
  
      fvp.on("core.form.valid", function () { 
        var formdata = new FormData(formUpdateProfile);
        var url = formUpdateProfile.getAttribute('action');
        var submitButton = $("button[type='submit']");
        
        toggleButtonLoadingState(submitButton, true);
    
        $.ajax({
            url: url,
            type: 'POST',
            processData: false, 
            contentType: false, 
            data: formdata,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {            
                if (response.status === "success") {
                    $(".is-invalid").removeClass("is-invalid"); // Remove validation error classes
                    $(".invalid-feedback").empty(); // Remove previous error messages
                    toastr.success(response.message, 'Success');
                } else {
                    toastr.error(response.message, 'Error');
                }
            },
            error: function (xhr, status, error) {
                if (xhr.status === 422) { 
                    let errors = xhr.responseJSON.errors;
    
                    // Remove existing validation messages before adding new ones
                    $(".is-invalid").removeClass("is-invalid");
                    $(".invalid-feedback").empty();
    
                    $.each(errors, function (key, value) {
                        let inputField = $("#" + key);
                        
                        if (inputField.length) {
                            inputField.addClass('is-invalid');
    
                            // Check if the input is inside `.input-group`
                            let errorMessage = '<div class="invalid-feedback">' + value[0] + '</div>';
                            if (inputField.closest(".input-group").length) {
                                inputField.closest(".input-group").after(errorMessage);
                            } else if (inputField.closest(".form-floating").length) {
                                inputField.closest(".form-floating").append(errorMessage);
                            } else {
                                inputField.after(errorMessage);
                            }
                        }
                    });
                }
            },
            complete: function () {
                toggleButtonLoadingState(submitButton, false);
            }
        });
    });
    
      
      
  
    }
  });
