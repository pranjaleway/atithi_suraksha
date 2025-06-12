'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const formAuthentication = document.querySelector('#loginForm');

  if (formAuthentication) {
    const fv = FormValidation.formValidation(formAuthentication, {
      fields: {
        email: {
          validators: {
            notEmpty: {
              message: 'Please enter your email'
            },
            emailAddress: {
              message: 'Please enter a valid email address'
            }
          }
        },
        password: {
          validators: {
            notEmpty: {
              message: 'Please enter your password'
            },
            stringLength: {
              min: 6,
              message: 'Password must be at least 6 characters'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: '.mb-3'
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });

    // Prevent normal form submission
   

      fv.on("core.form.valid", function () { 
        
          var email = $("#email").val();
          var password = $("#password").val();
          var url = formAuthentication.getAttribute('action');
          var submitButton = $("button[type='submit']");
          toggleButtonLoadingState(submitButton, true);

          $.ajax({
            url: url,
            type: 'POST',
            data: {
              email: email,
              password: password
            },
            headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
              if (response.success) {
                window.location.href = response.redirect;
                toastr.success(response.message, 'Success');
              } else {
                $('#loginError').removeClass('d-none');
                $('#loginError').text(response.message);
              }
            },
            error: function (xhr) {
                let errorMsg = 'Something went wrong. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }

                $('#loginError').removeClass('d-none').text(errorMsg);
            },
            complete: function () {
              toggleButtonLoadingState(submitButton, false);
            },
            statusCode: {
              422: function (data) {
                $.each(data.responseJSON, function (key, value) {
                  fv.showInvalidInput(key, value);
                });
              }
            }
          });
        
      });

  }
});
