/**
 * DataTables Basic
 */

flatpickr("#transfer_date", {
    dateFormat: "d/m/Y",
    maxDate: "today",
    allowInput: true,
});

("use strict");

var dt_basic_table = $(".datatables-basic"),
    dt_basic,
    canAdd;
// datatable (jquery)
$(function () {
    // DataTable with buttons
    // --------------------------------------------------------------------

    if (dt_basic_table.length) {
        let actionButtons = [];

        if (![1, 2, 3].includes(userRole)) {
            actionButtons = [
                {
                    text: '<i class="mdi mdi-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Transfer Bookings</span>',
                    className:
                        "create-new btn btn-primary waves-effect waves-light d-none me-2",
                    action: function () {
                        window.location.href = manualTransferEntriesUrl;
                    },
                },
                {
                    text: '<i class="mdi mdi-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add Register Record</span>',
                    className:
                        "create-new btn btn-label-primary waves-effect waves-light d-none",
                    action: function () {
                        //window.location.href = uploadedTransferEntriesUrl;
                        $("#addModal").modal("show");
                        $("#all-preview-row").empty();
                        $(".invalid-feedback").empty();
                        $(".is-invalid").removeClass("is-invalid");
                    },
                },
            ];
        }

        dt_basic = dt_basic_table.DataTable({
            ordering: true,
            ajax: {
                url: listUrl,
                type: "GET",
                datatype: "json",
                data: function (d) {
                    let dateRange = $("#flatpickr-range").val();
                    if (dateRange) {
                        const dates = dateRange.split(" to ");
                        d.from_date = dates[0];
                        d.to_date = dates[1];
                    }
                    const selectedHotel = $("#hotel-filter").val();
                    if (selectedHotel) {
                        d.hotel_id = selectedHotel;
                    }
                },
                dataSrc: function (json) {
                    json.data.forEach((element, index) => {
                        element.sequence_number = index + 1;
                        element.canEdit = json.canEdit || false;
                        element.canDelete = json.canDelete || false;
                    });

                    if (json.canAdd) {
                        $(".create-new").removeClass("d-none");
                    }

                    return json.data;
                },
            },
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    },
                },
                {
                    data: "hotel.hotel_name",
                    name: "hotel.hotel_name",
                },
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return data.hotelEmployee?.id > 0
                            ? "Employee"
                            : "Owner";
                    },
                },

                {
                    data: "transfer_date",
                    name: "transfer_date",
                    render: function (data, type, row, meta) {
                        if (!data) return "";
                        return new Date(data).toLocaleDateString("en-GB");
                    },
                },
                {
                    data: "created_at",
                    name: "created_at",
                    render: function (data, type, row, meta) {
                        if (!data) return "";
                        return new Date(data).toLocaleDateString("en-GB");
                    },
                },
            ],
            columnDefs: [
                {
                    targets: 5,
                    title: "Actions",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        var encodedId = btoa(full.hotel_id);
                        var transferDate = btoa(full.transfer_date);
                        var buttons = "";

                        if (full.transfer_types.includes("manual")) {
                            var manualUrl = bookingUrl
                                .replace("__ID__", encodedId)
                                .replace("__DATE__", transferDate);
                            buttons +=
                                '<a href="' +
                                manualUrl +
                                '" target="_blank" class="btn btn-primary btn-sm rounded-pill me-1">View Bookings</a>';
                        }

                        if (full.transfer_types.includes("uploaded")) {
                            var uploadedUrl = uploadedEntriesUrl
                                .replace("__ID__", encodedId)
                                .replace("__DATE__", transferDate);
                            buttons +=
                                '<a href="' +
                                uploadedUrl +
                                '" target="_blank" class="btn btn-info mt-1 btn-sm rounded-pill">View Uploaded</a>';
                        }

                        return buttons || "-";
                    },
                },
            ],
            // ðŸ‘‡ Modified layout to keep everything in a single row
            dom:
                '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>>' +
                '<"row align-items-center px-2"<"col-sm-12 col-md-3"l><"col-sm-12 col-md-5 extra-filters d-flex"><"col-sm-12 col-md-3"f>>' +
                't<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

            displayLength: 7,
            lengthMenu: [7, 10, 25, 50, 75, 100],
            buttons: actionButtons,
            scrollX: true,
        });

        $("div.head-label").html(
            '<h5 class="card-title mb-0">Transfer Bookings</h5>'
        );

        // ðŸ‘‡ Inject Flatpickr date range picker in the middle column
        if ([1, 2, 3].includes(userRole)) {
            $(".extra-filters").html(`
        <div class="form-floating form-floating-outline me-2">
            <input type="text" class="form-control flatpickr-input" id="flatpickr-range" placeholder="YYYY-MM-DD to YYYY-MM-DD" readonly>
            <label for="flatpickr-range">Date Range</label>
        </div>
        <div class="form-floating form-floating-outline">
            <select id="hotel-filter" class="form-select">
                <option value="">All Hotels</option>
                ${hotels
                    .map(
                        (hotel) =>
                            `<option value="${hotel.id}">${hotel.hotel_name}</option>`
                    )
                    .join("")}
            </select>
            <label for="hotel-filter">Hotel</label>
        </div>
    `);

            // Flatpickr init
            flatpickr("#flatpickr-range", {
                mode: "range",
                dateFormat: "Y-m-d",
                onChange: function () {
                    dt_basic.ajax.reload();
                },
            });

            // Reload on hotel change
            $("#hotel-filter").on("change", function () {
                dt_basic.ajax.reload();
            });
        }
    }

    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $(".dataTables_filter .form-control").removeClass("form-control-sm");
        $(".dataTables_length .form-select").removeClass("form-select-sm");
    }, 300);
});

document.addEventListener("DOMContentLoaded", function () {
    const formAuthentication = document.querySelector("#add-form");

    if (formAuthentication) {
        const fv = FormValidation.formValidation(formAuthentication, {
            fields: {
                file_path: {
                    validators: {
                        notEmpty: {
                            message: "Please upload at least one file",
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
                        $("#add-form")[0].reset();
                        dt_basic.ajax.reload();
                        $("#addModal").modal("hide");
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
