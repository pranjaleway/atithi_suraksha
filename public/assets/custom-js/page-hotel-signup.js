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
                        // license_number: {
                        //     validators: {
                        //         notEmpty: {
                        //             message: "Please enter license  number",
                        //         },
                        //     },
                        // },
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
                        police_station_id: {
                            validators: {
                                notEmpty: {
                                    message: "Please select police station",
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

                        let lastDocumentField = null;

                        $("#document_id").on("change", function () {
                            $("#all-preview-row").empty();

                            const documentId = $(this).val();
                            const documentName = $(this)
                                .find(":selected")
                                .data("name");

                            const fieldName = `document[${documentId}]`;

                            // Safely remove previous field from FormValidation BEFORE changing DOM
                            if (
                                lastDocumentField &&
                                multiSteps2
                                    .getFields()
                                    .hasOwnProperty(lastDocumentField)
                            ) {
                                multiSteps2.removeField(lastDocumentField);
                            }

                            if (!documentId) {
                                $("#documentUploadContainer").html("");
                                lastDocumentField = null;
                                return;
                            }

                            // HTML should be wrapped in .col-md-6 to support Bootstrap5 plugin layout
                            const fileInputHtml = `
            <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                    <input type="file" class="form-control document-input"
                        id="document_${documentId}" data-label="${documentName}"
                        name="${fieldName}" accept="image/*,application/pdf">
                    <label for="document_${documentId}">${documentName}</label>
                </div>
            </div>`;

                            $("#documentUploadContainer").html(fileInputHtml);

                            // Delay required to ensure new DOM element is ready before adding to validator
                            setTimeout(() => {
                                multiSteps2.addField(fieldName, {
                                    validators: {
                                        notEmpty: {
                                            message: "Please upload document",
                                        },
                                        file: {
                                            extension: "jpg,jpeg,png,pdf",
                                            type: "image/jpeg,image/png,image/jpg,application/pdf",
                                            message:
                                                "Only JPG, PNG, or PDF files are allowed",
                                        },
                                    },
                                });

                                lastDocumentField = fieldName;
                            }, 50);
                        });
                    },
                }
            ).on("core.form.valid", function () {
                let step1Data = new FormData(stepsValidationForm);

                $.ajax({
                    url: step1ValidationUrl,
                    type: "POST",
                    data: step1Data,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    success: function (response) {
                        if (response.status) {
                            validationStepper.next();
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;

                            $(".invalid-feedback").remove();
                            $(".is-invalid").removeClass("is-invalid");

                            let firstErrorElement = null;

                            $.each(errors, function (key, messages) {
                                let inputField = $("#" + key);
                                if (inputField.length) {
                                    inputField.addClass("is-invalid");
                                    inputField.after(
                                        `<div class="invalid-feedback">${messages[0]}</div>`
                                    );
                                    if (!firstErrorElement) {
                                        firstErrorElement = inputField;
                                    }
                                }
                            });

                            if (firstErrorElement) {
                                $("html, body").animate(
                                    {
                                        scrollTop:
                                            firstErrorElement.offset().top -
                                            100,
                                    },
                                    200
                                );
                            }
                        }
                    },
                });
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
                            },
                        },
                        document_id: {
                            validators: {
                                notEmpty: {
                                    message: "Please select document",
                                },
                            },
                        },
                        "document[]": {
                            validators: {
                                notEmpty: {
                                    message: "Please upload document",
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
                            $("#all-preview-row").empty();
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

let lastDocumentField;

$(document).on("change", "#city_id", function () {
    var id = $(this).val();
    if (id) {
        $.ajax({
            url: policeStationByCityUrl,
            type: "GET",
            data: { id: id },
            dataType: "json",
            success: function (response) {
                $("#police_station_id")
                    .empty()
                    .append('<option value="">Select Police Station</option>');

                if (response.data && response.data.length > 0) {
                    $.each(response.data, function (index, city) {
                        $("#police_station_id").append(
                            '<option value="' +
                                city.id +
                                '">' +
                                city.police_station_name +
                                "</option>"
                        );
                    });
                } else {
                    $("#police_station_id").append(
                        '<option value="">No Police Station Available</option>'
                    );
                }
            },
            error: function () {
                $("#police_station_id")
                    .empty()
                    .append("<option>Error loading Police Stations</option>");
            },
        });
    } else {
        $("#police_station_id")
            .empty()
            .append('<option value="">Select Police Station</option>');
    }
});
