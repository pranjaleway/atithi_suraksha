/**
 * DataTables Basic
 */

"use strict";

let offCanvasEl, fv;

document.addEventListener("DOMContentLoaded", function () {
    const formAddNewRecord = document.getElementById("addForm");

    setTimeout(() => {
        const newRecord = document.querySelector(".create-new"),
            offCanvasElement = document.querySelector("#add-new-record");

        if (newRecord) {
            newRecord.addEventListener("click", function () {
                offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
                $(".form-control").removeClass("is-invalid");
                $(".invalid-feedback").empty();
                $("#addForm").trigger("reset");

                if (typeof fv !== "undefined") {
                    fv.resetForm(true);
                }

                offCanvasEl.show();
            });
        }
    }, 200);

    if (formAddNewRecord) {
        fv = FormValidation.formValidation(formAddNewRecord, {
            fields: {
                title: {
                    validators: {
                        notEmpty: {
                            message: "Please enter title",
                        },
                    },
                },
                message: {
                    validators: {
                        notEmpty: {
                            message: "Please enter message",
                        },
                    },
                },
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: "",
                    rowSelector: ".form-floating, .form-select",
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus(),
            },
        });
    }
});

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
                    data: "user.name",
                    name: "user.name"
                },
                { data: "title", name: "title" },
                { data: "message", name: "message" },
            ],
            columnDefs: [
                {
                    // Actions
                    targets: 4,
                    title: "Actions",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        var deleteBtn = full.canDelete
                            ? '<div class="d-inline-block">' +
                              '<a href="javascript:;" class="dropdown-item text-danger delete-record" data-url = "' +
                              deleteUrl +
                              '"  data-id="' +
                              full.id +
                              '" ><i class="mdi mdi-delete"></i></a>' +
                              "</div>"
                            : "";

                        if (deleteBtn == "") {
                            return "Permission Denied";
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
                },
            ],
            scrollX: true,
        });

        $("div.head-label").html(
            '<h5 class="card-title mb-0">Notifications</h5>'
        );
    }

    //Submit Add form
    fv.on("core.form.valid", function () {
        var formdata = new FormData($("#addForm")[0]);
        var url = $("#addForm").attr("action");
        var submitButton = $("button[type='submit']");
        $(".invalid-feedback").empty();

        toggleButtonLoadingState(submitButton, true);

        $.ajax({
            url: url,
            type: "POST",
            data: formdata,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.status == "success") {
                    toastr.success(response.message, "Success");
                    offCanvasEl.hide();
                    dt_basic.ajax.reload();
                } else {
                    toastr.error("Something went wrong", "Error");
                }
            },
            error: function (xhr) {
                let response = xhr.responseJSON;

                if (xhr.status === 422 && response.errors) {
                    $(".invalid-feedback").remove();
                    $(".is-invalid").removeClass("is-invalid");

                    $.each(response.errors, function (key, value) {
                        let inputField = $("#" + key);
                        inputField.addClass("is-invalid");
                        inputField.after(
                            `<div class="invalid-feedback">${value}</div>`
                        );
                    });
                } else if (
                    response &&
                    response.status === "error" &&
                    response.message
                ) {
                    let inputField = $("#room_number");
                    inputField.addClass("is-invalid");
                    inputField.after(
                        `<div class="invalid-feedback">${response.message}</div>`
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


  
    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $(".dataTables_filter .form-control").removeClass("form-control-sm");
        $(".dataTables_length .form-select").removeClass("form-select-sm");
    }, 300);
});
