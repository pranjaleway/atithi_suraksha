/**
 * DataTables Basic
 */

"use strict";

var dt_basic_table = $(".datatables-basic"),
    dt_basic,
    canAdd;
// datatable (jquery)
$(function () {
    // DataTable with buttons
    // --------------------------------------------------------------------

    if (dt_basic_table.length) {
        dt_basic = dt_basic_table.DataTable({
            ordering: true,
            ajax: {
                url: listUrl,
                dataSrc: function (json) {
                    json.data.forEach((element, index) => {
                        element.sequence_number = index + 1;
                        element.canEdit = json.canEdit || false; // Ensure default value
                        element.canDelete = json.canDelete || false;
                    });

                    if (json.canAdd) {
                        $(".create-new").removeClass("d-none");
                    }

                    return json.data;
                },
                type: "GET",
                datatype: "json",
            },
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1; // sequence number
                    },
                },
                {
    data: "file_path",
    name: "file_path",
    render: function (data, type, row) {
        if (!data) return "-";

        const fileUrl = `/storage/${data}`;
        const fileExtension = data.split('.').pop().toLowerCase();

        let previewType = 'pdf';
        if (["jpg", "jpeg", "png", "gif", "bmp", "webp"].includes(fileExtension)) {
            previewType = 'image';
        }

        return `
            <a href="${fileUrl}" target="_blank" class="preview-file" data-url="${fileUrl}" data-type="${previewType}">
                Open File
            </a>
        `;
    }
}

            ],
            columnDefs: [
                {
                    // Actions
                    targets: 2,
                    title: "Actions",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        var id = btoa(full.id);
                       // var viewUrl = viewDetailsUrl.replace(":id", id);
                        var deleteBtn = full.canDelete
                            ? '<div class="d-inline-block">' +
                              '<a href="javascript:;" class="dropdown-item text-danger delete-record" data-url = "' +
                              deleteUrl +
                              '"  data-id="' +
                              full.id +
                              '" ><i class="mdi mdi-delete"></i></a>' +
                              "</div>"
                            : "";
                        // var editBtn = full.canEdit
                        //     ? '<a href="#" data-id="' +
                        //       full.id +
                        //       '" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-record"><i class="mdi mdi-pencil-outline"></i></a>'
                        //     : "";

                        // var viewBtn =
                        //     '<a href="'+ viewUrl +'" data-id="' +
                        //     full.id +
                        //     '" class="btn btn-sm btn-text-secondary rounded-pill btn-icon view-record"><i class="mdi mdi-eye-outline"></i></a>';

                        if (deleteBtn == "") {
                            return 'Permission Denied';
                        } else {
                            return deleteBtn;
                        }
                    },
                },
            ],
            dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 7,
            lengthMenu: [7, 10, 25, 50, 75, 100],
            buttons: [
                {
                    extend: "collection",
                    className:
                        "btn btn-label-primary dropdown-toggle me-2 waves-effect waves-light",
                    text: '<i class="mdi mdi-export-variant me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span>',
                    buttons: [
                        {
                            extend: "csv",
                            text: '<i class="mdi mdi-file-document-outline me-1" ></i>Csv',
                            className: "dropdown-item",
                        },
                        {
                            extend: "excel",
                            text: '<i class="mdi mdi-file-excel-outline me-1"></i>Excel',
                            className: "dropdown-item",
                        },

                        {
                            extend: "copy",
                            text: '<i class="mdi mdi-content-copy me-1" ></i>Copy',
                            className: "dropdown-item",
                        },
                    ],
                },
                {
                    text: '<i class="mdi mdi-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Record</span>',
                    className:
                        "create-new btn btn-primary waves-effect waves-light d-none",
                    action: function (e, dt, node, config) {
                        $("#addModal").modal("show");
                    },
                },
            ],
            scrollX: true,
        });

        $("div.head-label").html(
            '<h5 class="card-title mb-0">Uploaded Entries</h5>'
        );
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
                       $('#add-form')[0].reset();
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
