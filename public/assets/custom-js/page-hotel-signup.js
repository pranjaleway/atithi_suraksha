/**
 *  Page auth register multi-steps
 */

"use strict";

// Multi Steps Validation
// --------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", function (e) {
    (function () {
        const stepsValidation = document.querySelector("#multiStepsValidation");
        if (typeof stepsValidation !== undefined && stepsValidation !== null) {
            // Multi Steps form
            const stepsValidationForm =
                stepsValidation.querySelector("#multiStepsForm");
            // Form steps
            const stepsValidationFormStep1 = stepsValidationForm.querySelector(
                "#accountDetailsValidation"
            );
            const stepsValidationFormStep2 = stepsValidationForm.querySelector(
                "#personalInfoValidation"
            );
            // Multi steps next prev button
            const stepsValidationNext = [].slice.call(
                stepsValidationForm.querySelectorAll(".btn-next")
            );
            const stepsValidationPrev = [].slice.call(
                stepsValidationForm.querySelectorAll(".btn-prev")
            );

            let validationStepper = new Stepper(stepsValidation, {
                linear: true,
            });

            // Account details
            const multiSteps1 = FormValidation.formValidation(
                stepsValidationFormStep1,
                {
                    fields: {
                        hotel_name: {
                            validators: {
                                notEmpty: {
                                    message: "Please enter hotel name",
                                },
                                regexp: {
                                    regexp: /^[a-zA-Z0-9 ]+$/,
                                    message:
                                        "The name can only consist of alphabetical, number and space",
                                },
                            },
                        },
                        email: {
                            validators: {
                                notEmpty: {
                                    message: "Please enter email address",
                                },
                                emailAddress: {
                                    message:
                                        "The value is not a valid email address",
                                },
                            },
                        },
                        contact_number: {
                            validators: {
                                notEmpty: {
                                    message:
                                        "Please enter hotel contact  number",
                                },
                                stringLength: {
                                    min: 10,
                                    max: 10,
                                    message: "Mobile number must be 10 digits",
                                },
                                digits: {
                                    message:
                                        "Please enter a valid mobile number",
                                },
                            },
                        },
                        license_number: {
                            validators: {
                                notEmpty: {
                                    message: "Please enter license  number",
                                },
                            },
                        },
                        pincode: {
                            validators: {
                                notEmpty: {
                                    message: "Please enter pincode",
                                },
                                stringLength: {
                                    min: 6,
                                    max: 6,
                                    message: "Pincode must be 6 digits",
                                },
                                digits: {
                                    message: "Please enter a valid pincode",
                                },
                            },
                        },
                        state_id: {
                            validators: {
                                notEmpty: {
                                    message: "Please select state",
                                },
                            },
                        },
                        address: {
                            validators: {
                                notEmpty: {
                                    message: "Please enter your address",
                                },
                            },
                        },
                        city_id: {
                            validators: {
                                notEmpty: {
                                    message: "Please select city",
                                },
                            },
                        },
                        password: {
                            validators: {
                                notEmpty: {
                                    message: "Please enter your password",
                                },
                                stringLength: {
                                    min: 6,
                                    message:
                                        "Password must be at least 6 characters",
                                },
                            },
                        },
                        password_confirmation: {
                            validators: {
                                notEmpty: {
                                    message: "Please confirm your password",
                                },
                                identical: {
                                    compare: function () {
                                        return stepsValidationForm.querySelector(
                                            '[name="password"]'
                                        ).value;
                                    },
                                    message: "Passwords do not match",
                                },
                            },
                        },
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5({
                            // Use this for enabling/changing valid/invalid class
                            // eleInvalidClass: '',
                            eleValidClass: "",
                            rowSelector: ".col-sm-6",
                        }),
                        autoFocus: new FormValidation.plugins.AutoFocus(),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                    },
                    init: (instance) => {
                        instance.on("plugins.message.placed", function (e) {
                            if (
                                e.element.parentElement.classList.contains(
                                    "input-group"
                                )
                            ) {
                                e.element.parentElement.insertAdjacentElement(
                                    "afterend",
                                    e.messageElement
                                );
                            }
                        });
                    },
                }
            ).on("core.form.valid", function () {
                // Jump to the next step when all fields in the current step are valid
                validationStepper.next();
            });

            // Personal info
            const multiSteps2 = FormValidation.formValidation(
                stepsValidationFormStep2,
                {
                    fields: {
                        owner_name: {
                            validators: {
                                notEmpty: {
                                    message: "Please enter owner name",
                                },
                                regexp: {
                                    regexp: /^[a-zA-Z0-9 ]+$/,
                                    message:
                                        "The name can only consist of alphabetical, number and space",
                                },
                            },
                        },
                        owner_contact_number: {
                            validators: {
                                notEmpty: {
                                    message:
                                        "Please enter hotel owner contact  number",
                                },
                                stringLength: {
                                    min: 10,
                                    max: 10,
                                    message: "Mobile number must be 10 digits",
                                },
                                digits: {
                                    message:
                                        "Please enter a valid mobile number",
                                },
                            },
                        },
                        aadhar_number: {
                            validators: {
                                notEmpty: {
                                    message: "Please enter aadhar  number",
                                },
                                stringLength: {
                                    min: 12,
                                    max: 12,
                                    message: "Aadhar number must be 12 digits",
                                },
                                digits: {
                                    message:
                                        "Please enter a valid aadhar number",
                                },
                            },
                        },
                        pan_number: {
                            validators: {
                                notEmpty: {
                                    message: "Please enter pan  number",
                                },
                                stringLength: {
                                    min: 10,
                                    max: 10,
                                    message: "Pan number must be 10 digits",
                                },
                                digits: {
                                    message: "Please enter a valid pan number",
                                },
                            },
                        },
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5({
                            // Use this for enabling/changing valid/invalid class
                            // eleInvalidClass: '',
                            eleValidClass: "",
                            rowSelector: ".col-sm-6",
                        }),
                        autoFocus: new FormValidation.plugins.AutoFocus(),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                    },
                }
            ).on("core.form.valid", function () {
                var formdata = new FormData(stepsValidationForm);
                var submitButton = $("button[type='submit']");
                $(".invalid-feedback").empty();

                toggleButtonLoadingState(submitButton, true);

                $.ajax({
                    url: signupUrl,
                    type: "POST",
                    data: formdata,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            toastr.success(response.message, "Success");
                           stepsValidationForm.reset();
                            validationStepper.to(0);
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $(".invalid-feedback").remove(); // Remove previous errors
                            $(".is-invalid").removeClass("is-invalid"); // Reset validation classes

                            let firstErrorElement = null;

                            $.each(errors, function (key, value) {
                                let inputField = $("#" + key);
                                inputField.addClass("is-invalid");
                                inputField.after(
                                    `<div class="invalid-feedback">${value}</div>`
                                );
                                if (!firstErrorElement) {
                                    firstErrorElement = inputField;
                                }
                            });

                            // Scroll to the first error field smoothly
                            if (firstErrorElement) {
                                $("html, body").animate(
                                    {
                                        scrollTop:
                                            firstErrorElement.offset().top -
                                            100, // Adjust offset if needed
                                    },
                                    200
                                );
                            }
                        } else if (
                            xhr.responseJSON &&
                            xhr.responseJSON.message.includes("Duplicate entry")
                        ) {
                            let inputField = $("#email");
                            inputField.addClass("is-invalid");
                            inputField.after(
                                `<div class="invalid-feedback">This email is already in use.</div>`
                            );

                            // Scroll to the email field
                            $("html, body").animate(
                                {
                                    scrollTop: inputField.offset().top - 100,
                                },
                                200
                            );
                        } else {
                            toastr.error("Something went wrong", "Error");
                        }
                    },

                    complete: function () {
                        toggleButtonLoadingState(submitButton, false);
                    },
                });
            });

            stepsValidationNext.forEach((item) => {
                item.addEventListener("click", (event) => {
                    // When click the Next button, we will validate the current step
                    switch (validationStepper._currentIndex) {
                        case 0:
                            multiSteps1.validate();
                            break;

                        case 1:
                            multiSteps2.validate();
                            break;

                        default:
                            break;
                    }
                });
            });

            stepsValidationPrev.forEach((item) => {
                item.addEventListener("click", (event) => {
                    switch (validationStepper._currentIndex) {
                        case 2:
                            validationStepper.previous();
                            break;

                        case 1:
                            validationStepper.previous();
                            break;

                        case 0:

                        default:
                            break;
                    }
                });
            });
        }
    })();
});
