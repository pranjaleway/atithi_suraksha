$(function () {
    var maxlengthInput = $(".bootstrap-maxlength-example"),
        formRepeater = $(".form-repeater");

    // Bootstrap Max Length
    // --------------------------------------------------------------------
    if (maxlengthInput.length) {
        maxlengthInput.each(function () {
            $(this).maxlength({
                warningClass: "label label-success bg-success text-white",
                limitReachedClass: "label label-danger",
                separator: " out of ",
                preText: "You typed ",
                postText: " chars available.",
                validate: true,
                threshold: +this.getAttribute("maxlength"),
            });
        });
    }

    // Form Repeater
    // ! Using jQuery each loop to add dynamic id and class for inputs. You may need to improve it based on form fields.
    // -----------------------------------------------------------------------------------------------------------------

    if ($(".form-repeater").length) {
        let row = 2;
        $(".form-repeater").repeater({
            show: function () {
                const $newGroup = $(this);

                // Fix ALL checkboxes from index 0 to current count
                $(".repeat").each(function (i) {
                    const checkbox = $(this).find(".same-address-checkbox");
                    checkbox.attr("name", `guests[${i}][same_address]`);
                });

                const currentIndex = $(".repeat").length - 1;

                // Show checkbox section only for additional guests
                if (currentIndex >= 1) {
                    $newGroup.find(".common-fields").hide();
                    $newGroup
                        .find(".same-address-wrapper")
                        .removeClass("d-none");
                }

                // Checkbox logic
                const checkbox = $newGroup.find(".same-address-checkbox");
                checkbox.prop("checked", true);

                checkbox.off("change").on("change", function () {
                    const isChecked = $(this).is(":checked");
                    $newGroup.find(".address-fields").toggle(!isChecked);
                });

                // Reset all other inputs in the new form
                $newGroup
                    .find("input, textarea, select")
                    .not(checkbox)
                    .val("")
                    .prop("checked", false);

                $newGroup.find(".address-fields").hide();
                $newGroup.find(".deleteDiv").removeClass("d-none");

                $(this).slideDown();
            },

            hide: function (deleteElement) {
                if (confirm("Are you sure you want to delete this entry?")) {
                    $(this).slideUp(deleteElement);
                }
            },
        });
    }
});

$(document).on("change", ".id-proof-input", function () {
    var input = this;
    var previewContainer = $(this).closest(".mb-3").find(".preview");
    previewContainer.html(""); // Clear previous preview

    if (input.files && input.files[0]) {
        var file = input.files[0];
        var fileType = file.type;

        if (fileType.startsWith("image/")) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = $("<img>", {
                    src: e.target.result,
                    class: "img-fluid rounded border",
                    width: 150,
                    alt: "Image Preview",
                });
                previewContainer.append(img);
            };
            reader.readAsDataURL(file);
        } else if (fileType === "application/pdf") {
            var fileURL = URL.createObjectURL(file);
            var iframe = $("<iframe>", {
                src: fileURL,
                width: "100%",
                height: "200px",
                frameborder: "0",
            }).css("border", "1px solid #ccc");

            previewContainer.append(iframe);
        } else {
            previewContainer.text("Unsupported file type.");
        }
    }
});

$(document).ready(function () {
    // Main submit function
    $("#add-form").on("submit", function (e) {
        e.preventDefault();

        if (validateAddForm()) {
            const formData = new FormData();
            let hasFiles = false;

            $("div[data-repeater-item]").each(function (index) {
                $(this)
                    .find("input[name], select[name], textarea[name]")
                    .each(function () {
                        const input = $(this);
                        const fullName = input.attr("name");
                        if (!fullName) return;

                        // Extract the second part from group-a[0][field_name][]
                        // This regex captures the part inside the second []
                        const match = fullName.match(/\[\d+\]\[([^\]]+)\]/);
                        const key = match ? match[1] : fullName;

                        if (input.is("select[multiple]")) {
                            let selectedValues = input.val() || [];
                            formData.append(
                                `guests[${index}][${key}]`,
                                selectedValues.join(",")
                            );
                        } else if (
                            input.attr("type") === "file" &&
                            input[0].files.length > 0
                        ) {
                            formData.append(
                                `guests[${index}][${key}]`,
                                input[0].files[0]
                            );
                        } else if (input.attr("type") === "checkbox") {
                            formData.append(
                                `guests[${index}][${key}]`,
                                input.is(":checked") ? 1 : 0
                            );
                        } else {
                            formData.append(
                                `guests[${index}][${key}]`,
                                input.val()
                            );
                        }
                    });
            });

            let url = $("#add-form").attr("action");
            let submitButton = $("button[type='submit']");
            toggleButtonLoadingState(submitButton, true);

            $.ajax({
                url: url,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function (res) {
                    if (res.status === "success") {
                        toastr.success("Guests saved successfully.");
                        window.location.href = res.redirect;
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $(".invalid-feedback").remove(); // Clear previous errors
                        $(".is-invalid").removeClass("is-invalid");

                        $.each(errors, function (key, messages) {
                            const parts = key.split(".");
                            const index = parts[1];
                            const field = parts[2];

                            const input = $(
                                `[name="group-a[${index}][${field}]"]`
                            );
                            input.addClass("is-invalid");
                            input.after(
                                `<div class="invalid-feedback">${messages[0]}</div>`
                            );
                        });
                    }
                },
                complete: function () {
                    toggleButtonLoadingState(submitButton, false);
                },
            });
        }
    });
});

function validateAddForm() {
    var isValid = true;
    // Reset error messages
    $(".invalid-feedback").text("");

    $(".repeat").each(function (index) {
        // Make sure isValid is defined and initialized

        var $fields = $(this).find(
            "[id^='check_in-'], [id^='check_out-'], [id^='guest_name-'], [id^='pincode-'],  [id^='address-'],  [id^='aadhar_number-'], [id^='contact_number-'], [id^='room_number-']"
        );

        $fields.each(function () {
            var $field = $(this);
            var fieldName = $field.attr("id").split("-")[0];
            var errorMessage =
                "The " + fieldName.replace("_", " ") + " is required";

            if ($field.val().trim() === "") {
                isValid = false;
                $field.addClass("is-invalid");
                $field.after(
                    '<div class="invalid-feedback">' + errorMessage + "</div>"
                );
            } else {
                $field.removeClass("is-invalid");
            }
        });
        var checkInID = $(this).find("[id^='check_in-']");
        if (checkInID.length > 0) {
            // Check if any elements are found
            var checkIn = checkInID.val();
            if (checkIn !== undefined && checkIn.trim() !== "") {
                // Check if the value is defined and not empty
                var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
                if (!dateRegex.test(checkIn)) {
                    isValid = false;
                    checkInID.addClass("is-invalid");
                    checkInID.after(
                        '<div class="invalid-feedback">Please enter a valid date in the format YYYY-MM-DD</div>'
                    );
                } else {
                    checkInID.removeClass("is-invalid");
                }
            }
        }
    });

    return isValid;
}

$(document).on("change", "[id^='state_id-']", function () {
    var $stateSelect = $(this);
    var stateId = $stateSelect.val();

    // Find the city select in the same repeater group
    var $citySelect = $stateSelect
        .closest("[data-repeater-item]")
        .find("[id^='city_id-']");

    if (stateId) {
        $.ajax({
            url: cityUrl, // make sure `cityUrl` is defined globally
            type: "GET",
            data: { state_id: stateId },
            dataType: "json",
            success: function (response) {
                $citySelect
                    .empty()
                    .append('<option value="">Select City</option>');

                $.each(response.data, function (index, city) {
                    $citySelect.append(
                        '<option value="' +
                            city.id +
                            '">' +
                            city.name +
                            "</option>"
                    );
                });
            },
            error: function () {
                $citySelect
                    .empty()
                    .append("<option>Error loading cities</option>");
            },
        });
    } else {
        $citySelect.empty().append('<option value="">Select City</option>');
    }
});

$(document).on("input", "#no_of_guest", function () {
    const rawValue = $(this).val();
    const guestCountForRepeater = parseInt(rawValue) || 1;
    const guestCountForVisibility = parseInt(rawValue);

    const $form = $(this).closest("form");
    const $addButton = $form.find("[data-repeater-create]");
    const $repeaterList = $form.find("[data-repeater-list]");
    const $repeaterItems = $repeaterList.find("[data-repeater-item]");

    // Show/hide count fields
    if (guestCountForVisibility > 0) {
        $(".count-fields").removeClass("d-none");
    } else {
        $(".count-fields").addClass("d-none");
    }

    if (guestCountForVisibility > 1){
        $('.showOtherGuestsDiv').removeClass('d-none');
        $("#showOtherGuests").trigger("change"); 
    } else {
        $('.showOtherGuestsDiv').addClass('d-none');
    }

    // // If input is empty or invalid, remove all repeater items except the first one
    // if (!rawValue || guestCountForVisibility <= 0) {
    //     $repeaterItems.each(function (index) {
    //         if (index > 0) {
    //             $(this).slideUp(function () {
    //                 $(this).remove();
    //             });
    //         }
    //     });
    //     return;
    // }

    // // Add/remove logic
    // const currentCount = $repeaterList.find("[data-repeater-item]").length;

    // if (guestCountForRepeater > currentCount) {
    //     const timesToAdd = guestCountForRepeater - currentCount;
    //     for (let i = 0; i < timesToAdd; i++) {
    //         $addButton.trigger("click");
    //     }
    // } else if (guestCountForRepeater < currentCount) {
    //     const timesToRemove = currentCount - guestCountForRepeater;
    //     for (let i = 0; i < timesToRemove; i++) {
    //         $repeaterList
    //             .find("[data-repeater-item]")
    //             .last()
    //             .slideUp(function () {
    //                 $(this).remove();
    //             });
    //     }
    // }
});

$(document).on("change", ".check_in", function () {
    const checkIn = $(this).val();
    if (!checkIn) return;

    $.ajax({
        url: getRoomUrl,
        type: "GET",
        data: {
            check_in: checkIn,
        },
        success: function (res) {
            if (res.status && res.data) {
                const $select = $("#room_number_id");
                $select
                    .empty()
                    .append(
                        '<option value="" disabled>Select Room Number</option>'
                    );

                res.data.forEach((room) => {
                    $select.append(
                        `<option value="${room.id}">${room.room_number}</option>`
                    );
                });
            }
        },
        error: function () {
            alert("Failed to load available rooms.");
        },
    });
});

const room_number_id = $(".room_number_id");
if (room_number_id.length) {
    room_number_id.each(function () {
        var $this = $(this);
        select2Focus($this);
        $this.wrap('<div class="position-relative"></div>').select2({
            placeholder: "Select Room Number",
            dropdownCssClass: "select2-scrollable",
        });
    });
}

$(document).on("change", "#showOtherGuests", function () {
    const guestCount = parseInt($("#no_of_guest").val()) || 1;
    const $form = $(this).closest("form");
    const $addButton = $form.find("[data-repeater-create]");
    const $repeaterList = $form.find("[data-repeater-list]");
    const $repeaterItems = $repeaterList.find("[data-repeater-item]");

    if ($(this).is(":checked") && guestCount > 1) {
        const currentCount = $repeaterItems.length;
        const toAdd = guestCount - currentCount;

        for (let i = 0; i < toAdd; i++) {
            $addButton.trigger("click");
        }
    } else {
        $repeaterItems.each(function (index) {
            if (index > 0) {
                $(this).remove();
            }
        });
    }
});

// Re-trigger checkbox logic when no_of_guest changes
$(document).on("input", "#no_of_guest", function () {
    $("#showOtherGuests").trigger("change");
});



