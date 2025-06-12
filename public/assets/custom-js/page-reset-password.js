'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const formAuthentication = document.querySelector('#resetPasswordForm');

  if (formAuthentication) {
    const fv = FormValidation.formValidation(formAuthentication, {
      fields: {
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
        },
        'confirm-password': {
                validators: {
                    notEmpty: {
                        message: 'Please confirm your password'
                    },
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
      var url = formAuthentication.getAttribute('action');
      var submitButton = $("button[type='submit']");
      var password = $('#password').val();
      var confirmPassword = $('#confirm-password').val();
  
      // Extract token from the URL path
      var pathSegments = window.location.pathname.split('/');
      var token = pathSegments[pathSegments.length - 1]; // Last part of the path
  
      // Extract email from the query string
      var email = new URLSearchParams(window.location.search).get('email');
  
      var data = {
          email: email,
          password: password,
          'confirm-password': confirmPassword,
          token: token
      };
  
      toggleButtonLoadingState(submitButton, true);
  
      $.ajax({
          url: url,
          type: 'POST',
          data: data,
          headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
          success: function (response) {
              if (response.success) {
                  toastr.success(response.message, 'Success');
                  window.location.href = response.redirect;
              } else {
                  $('#resetPasswordError').removeClass('d-none').text('Invalid Email. Please try again.');
              }
          },
          complete: function () {
              toggleButtonLoadingState(submitButton, false);
          },
          error: function (xhr) {
              if (xhr.status === 422) { 
                  let errors = xhr.responseJSON.errors;
                  
                  $.each(errors, function (key, value) {
                      let inputField = $("#" + key); 
                      let lastSibling = inputField.nextAll().last();
  
                      inputField.addClass('is-invalid');
  
                      lastSibling.after(`
                          <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                              <div data-field="${key}" data-validator="emailAddress">${value[0]}</div>
                          </div>
                      `);
                  });
              } else {
                  $('#resetPasswordError').removeClass('d-none').text('Something went wrong. Please try again.');
              }
          }
      });
  });
  
    
    

  }
});
