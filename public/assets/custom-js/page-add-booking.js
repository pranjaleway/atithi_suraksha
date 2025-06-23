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

    if (formRepeater.length) {
        var row = 2; // Initial row count
        var col = 1; // Initial column count
        // Assuming this is where the form repeater adds new input fields
        // function addNewInputField() {
        //   // Code to add new input field
        //   // After adding the input field, reapply the datepicker
        //   $('[id^="check_in-"]').datepicker({
        //     format: "yyyy-mm-dd",
        //     todayHighlight: true,
        //     autoclose: true,
        //     orientation: isRtl ? "auto right" : "auto left",
        //   });
        // }

        formRepeater.repeater({
            defaultValues: {
                "textarea-input": "",
                "text-input": "",
                "select-input": null,
                "checkbox-input": [],
                "radio-input": null,
            },

            show: function () {
                var $newGroup = $(this)
                    .closest(".add-new-record")
                    .find("[data-repeater-item]:last");
                //addNewInputField();
                // Set default values for input fields
                $newGroup.find('input[type="text"]').val("");
                $newGroup.find('input[type="checkbox"]').prop("checked", false);
                $newGroup.find("select").val(null).trigger("change");
                $newGroup.find('input[type="radio"]').prop("checked", false);

                var errorSpans = $newGroup.find(".text-danger");
                errorSpans.each(function (i) {
                    var fieldName = $(this).attr("id").split("Error")[0];
                    var errorId = fieldName + "Error_" + (row - 1);
                    $(this).attr("id", errorId);
                });

                var formGroup = $(this).find(".repeat").last();
                var fromControl = formGroup.find(
                    ".form-control, .form-select, .form-check-input"
                );
                var formLabel = formGroup.find("label");

                fromControl.each(function (i) {
                    var inputName = $(this).attr("name");
                    var fieldName = inputName.split("[").pop().split("]")[0];
                    var id = fieldName + "-" + row + "-" + col;
                    $(this).attr("id", id);
                    formLabel.eq(i).attr("for", id);
                    col++;
                });

                row++;

                col = 1; // Reset column count for the next row

                $(this).slideDown();
            },
            hide: function (deleteElement) {
                if (confirm("Are you sure you want to delete this element?")) {
                    $(this).slideUp(deleteElement);
                }
            },
        });
    }
});

$(document).on('change', '.id-proof-input', function () {
    var input = this;
    var previewContainer = $(this).closest('.mb-3').find('.preview');
    previewContainer.html(""); // Clear previous preview

    if (input.files && input.files[0]) {
        var file = input.files[0];
        var fileType = file.type;

        if (fileType.startsWith("image/")) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = $('<img>', {
                    src: e.target.result,
                    class: 'img-fluid rounded border',
                    width: 150,
                    alt: 'Image Preview'
                });
                previewContainer.append(img);
            };
            reader.readAsDataURL(file);
        } else if (fileType === "application/pdf") {
            var fileURL = URL.createObjectURL(file);
            var iframe = $('<iframe>', {
                src: fileURL,
                width: "100%",
                height: "200px",
                frameborder: "0"
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
                .find("input, select, textarea")
                .each(function () {
                    let input = $(this);
                    let name = input.attr("name");
                    let value = input.val();

                    // Adjust input name to guests[index][field]
                    let match = name.match(/\[?([^\]]+)\]?$/);
                    let key = match ? match[1] : name;

                    if (input.attr("type") === "file" && input[0].files.length > 0) {
                        hasFiles = true;
                        formData.append(`guests[${index}][${key}]`, input[0].files[0]);
                    } else {
                        formData.append(`guests[${index}][${key}]`, value);
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
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
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

                        const input = $(`[name="group-a[${index}][${field}]"]`);
                        input.addClass("is-invalid");
                        input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                    });
                }
            },
            complete: function () {
                toggleButtonLoadingState(submitButton, false);
            }
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
            "[id^='check_in-'], [id^='check_out-'], [id^='guest_name-'], [id^='pincode-'], [id^='city_id-'], [id^='state_id-'],  [id^='address-'],  [id^='aadhar_number-'], [id^='contact_number-'], [id^='room_number-'], [id^='id_proof-']"
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
        .closest('[data-repeater-item]')
        .find("[id^='city_id-']");

    if (stateId) {
        $.ajax({
            url: cityUrl, // make sure `cityUrl` is defined globally
            type: "GET",
            data: { state_id: stateId },
            dataType: "json",
            success: function (response) {
                $citySelect.empty().append('<option value="">Select City</option>');

                $.each(response.data, function (index, city) {
                    $citySelect.append(
                        '<option value="' + city.id + '">' + city.name + "</option>"
                    );
                });
            },
            error: function () {
                $citySelect.empty().append("<option>Error loading cities</option>");
            },
        });
    } else {
        $citySelect.empty().append('<option value="">Select City</option>');
    }
});

