'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const formAuthentication = document.querySelector('#forgotPasswordForm');

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
        var emailInput = $("#email");
        var url = formAuthentication.getAttribute('action');
        var submitButton = $("button[type='submit']");
        
        emailInput.removeClass('is-invalid');
        $(".invalid-feedback").remove();
    
        toggleButtonLoadingState(submitButton, true);
    
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                email: emailInput.val(),
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message, 'Success');
                } else {
                    $('#forgotPasswordError').removeClass('d-none').text('Invalid Email. Please try again.');
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
    
                        inputField.addClass('is-invalid');
    
                        inputField.after(`
                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                <div data-field="${key}" data-validator="emailAddress">${value[0]}</div>
                            </div>
                        `);
                    });
                }
            }
        });
    });
    
    

  }
});
