"use strict";

document.addEventListener("DOMContentLoaded", function () {
    const formAuthentication = document.querySelector("#add-edit-form");

    if (formAuthentication) {
        const fv = FormValidation.formValidation(formAuthentication, {
            fields: {
                office_name: {
                    validators: {
                        notEmpty: {
                            message: "Please enter office name",
                        },
                    },
                },
                email: {
                    validators: {
                        notEmpty: {
                            message: "Please enter email",
                        },
                        emailAddress: {
                            message: "Please enter a valid email address",
                        },
                    },
                },
                contact_number: {
                    validators: {
                        notEmpty: {
                            message: "Please enter contact  number",
                        },
                        stringLength: {
                            min: 10,
                            max: 10,
                            message: "Mobile number must be 10 digits",
                        },
                        digits: {
                            message: "Please enter a valid mobile number",
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
                            message: "Password must be at least 6 characters",
                        },
                    },
                },
                password_confirmation : {
                    validators: {
                        notEmpty: {
                            message: "Please confirm your password",
                        },
                        identical: {
                            compare: function () {
                                return formAuthentication.querySelector(
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
                    eleValidClass: "",
                    rowSelector: ".col-md-6",
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                // Submit the form when all fields are valid
                // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                autoFocus: new FormValidation.plugins.AutoFocus(),
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
        });

        // Prevent normal form submission
        fv.on("core.form.valid", function () {
            var formdata = new FormData(formAuthentication);
            var url = formAuthentication.getAttribute("action");
            var submitButton = $("button[type='submit']");
            $(".invalid-feedback").empty();

            toggleButtonLoadingState(submitButton, true);

            $.ajax({
                url: url,
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
                        formAuthentication.reset(); // Reset form on success
                        //fv.resetForm(true);
                        window.location.href = response.redirect;
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
                                        firstErrorElement.offset().top - 100, // Adjust offset if needed
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
    }
});
