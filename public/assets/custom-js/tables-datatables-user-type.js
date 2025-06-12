/**
 * DataTables Basic
 */

"use strict";

let offCanvasEl, fv;

document.addEventListener("DOMContentLoaded", function () {
    const formAddNewRecord = document.getElementById("userTypeForm");

    setTimeout(() => {
        const newRecord = document.querySelector(".create-new"),
            offCanvasElement = document.querySelector("#add-new-record");

        if (newRecord) {
            newRecord.addEventListener("click", function () {
                offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
                $(".form-control").removeClass("is-invalid");
                $(".invalid-feedback").empty();
                $("#userTypeForm").trigger("reset");
                // Reset validation messages
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
                user_type: {
                    validators: {
                        notEmpty: {
                            message: "Please enter user type",
                        },
                    },
                },
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: "",
                    rowSelector: ".form-floating",
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus(),
            },
        });
    }
});


var dt_basic_table = $(".datatables-basic"),
dt_basic, canAdd;
// datatable (jquery)
$(function () {
   

    // DataTable with buttons
    // --------------------------------------------------------------------

    if (dt_basic_table.length) {
    
        dt_basic = dt_basic_table.DataTable({
            ordering: true,
            ajax: {
                url: "/user-type",
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
                type: "GET",
                datatype: "json",
            },
            columns: [
                { data: "sequence_number" },
                { data: "user_type" },
                {
                    data: "status",
                    render: function (data, type, row) {
                        return data == 0
                            ? `<label class="switch switch-primary">
                                <input type="checkbox" class="switch-input status_${row.id}" onclick="changeStatus(${row.id})" data-id="${row.id}" data-url="${changeStatusURl}" name="status">
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"></span>
                                    <span class="switch-off"></span>
                                </span>
                            </label>`
                            : `<label class="switch switch-primary">
                                <input type="checkbox" class="switch-input status_${row.id}" onclick="changeStatus(${row.id})" data-id="${row.id}" data-url="${changeStatusURl}" checked name="status">
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"></span>
                                    <span class="switch-off"></span>
                                </span>
                            </label>`;
                    },
                },
            ],
            columnDefs: [
                ...(userRole == 0 || userRole == 1 ? [
                    {
                        targets: 3,
                        title: "Access",
                        orderable: false,
                        searchable: false,
                        render: function (data, type, full, meta) {
                            return '<a href="/user-access/' + btoa(full.id) + '" target="_blank" class="btn btn-primary btn-sm rounded-pill access-record" data-id="' +
                                full.id + '">User Access</a>';
                        },
                    }
                ] : []), // Conditionally add "Access" column only if role is 0
                {
                    targets: userRole == 0 || userRole == 1 ? 4 : 3, // Adjust index if "Access" column is hidden
                    title: "Actions",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        var deleteBtn = full.canDelete
                            ? '<div class="d-inline-block">' + 
                            '<a href="javascript:;" class="dropdown-item text-danger delete-record" data-url="' + deleteUrl + '" data-id="' +
                            full.id + '"><i class="mdi mdi-delete"></i></a></div>'
                            : "";
    
                        var editBtn = full.canEdit
                            ? '<a href="javascript:;" data-id="' + full.id + '" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-record"><i class="mdi mdi-pencil-outline"></i></a>'
                            : "";

                        if(deleteBtn == "" && editBtn == "") {
                            return "Permission Denied";
                        } else {
                            return editBtn + deleteBtn;
                        }
                    },
                },
            ],
            rowCallback: function (row, data, index) {
                var pageInfo = dt_basic.page.info();
                var currentPage = pageInfo.page + 1;
                var pageSize = pageInfo.length;
                var sequenceNumber = (currentPage - 1) * pageSize + index + 1;
        
                $(row).find("td:eq(0)").html(sequenceNumber);
            },
            dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 7,
            lengthMenu: [7, 10, 25, 50, 75, 100],
            buttons: [
                {
                    extend: "collection",
                    className: "btn btn-label-primary dropdown-toggle me-2 waves-effect waves-light",
                    text: '<i class="mdi mdi-export-variant me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span>',
                    buttons: [
                        {
                            extend: "csv",
                            text: '<i class="mdi mdi-file-document-outline me-1"></i>Csv',
                            className: "dropdown-item",
                        },
                        {
                            extend: "excel",
                            text: '<i class="mdi mdi-file-excel-outline me-1"></i>Excel',
                            className: "dropdown-item",
                        },
                        {
                            extend: "copy",
                            text: '<i class="mdi mdi-content-copy me-1"></i>Copy',
                            className: "dropdown-item",
                        },
                    ],
                },
                {
                    text: '<i class="mdi mdi-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Record</span>',
                    className: "create-new btn btn-primary waves-effect waves-light d-none",
                },
            ],
            scrollX: true,
        });
    
        $("div.head-label").html('<h5 class="card-title mb-0">User Type</h5>');
    }
    

    //Submit Add form
    fv.on("core.form.valid", function () {
        var user_type = $("#user_type").val();
        var url = $("#userTypeForm").attr("action");
        var submitButton = $("button[type='submit']");
        $(".invalid-feedback").empty();

        toggleButtonLoadingState(submitButton, true);

        $.ajax({
            url: url,
            type: "POST",
            data: {
               user_type : user_type,
            },
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
            complete: function () {
                toggleButtonLoadingState(submitButton, false);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        let inputField = $("#" + key);
                        inputField.addClass("is-invalid");

                        inputField.after(`
            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                <div data-field="${key}" data-validator="notEmpty">${value[0]}</div>
            </div>
          `);
                    });
                } else {
                    toastr.error("Something went wrong", "Error");
                }
            },
        });
    });

    //Edit record

    const offCanvasElementEdit = document.querySelector("#userTypeEdit");
    const offCanvasElEdit = new bootstrap.Offcanvas(offCanvasElementEdit);

    $(".datatables-basic tbody").on("click", ".edit-record", function () {
        const id = $(this).data("id");
        if (id) {
            offCanvasElEdit.show();
            $(".form-control").removeClass("is-invalid");
            $(".invalid-feedback").empty();

            $.ajax({
                url: "/edit-user-type/" + id,
                type: "GET",
                dataType: "json",
                success: function (response) {
                    $("#edit_user_type").val(response.data.user_type);
                    $("#edit_id").val(response.data.id);
                },
                error: function (xhr) {
                    toastr.error("Something went wrong", "Error");
                },
            });
        }
    });

    //Submit edit form

    const formEditRecord = document.getElementById("userTypeEditForm");
    let fve;

    // Initialize form validation only once

    fve = FormValidation.formValidation(formEditRecord, {
        fields: {
            user_type: {
                validators: {
                    notEmpty: {
                        message: "Please enter user type",
                    },
                },
            },
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleValidClass: "",
                rowSelector: ".form-floating",
            }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
    });

    fve.on("core.form.valid", function () {
        var id = $("#edit_id").val();
        var user_type = $("#edit_user_type").val();
        var url = $("#userTypeEditForm").attr("action");
        var submitButton = $("button[type='submit']");
        toggleButtonLoadingState(submitButton, true);

        $.ajax({
            url: url,
            type: "PUT",
            data: {
                id: id,
                user_type: user_type,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    toastr.success("Bank updated successfully", "Success");
                    dt_basic.ajax.reload();
                    offCanvasElEdit.hide();
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $(".invalid-feedback").remove(); // Remove previous errors
                    $(".is-invalid").removeClass("is-invalid"); // Reset validation classes

                    $.each(errors, function (key, value) {
                        let inputField = $("#edit_" + key);
                        inputField.addClass("is-invalid");
                        inputField.after(
                            `<div class="invalid-feedback">${value}</div>`
                        );
                    });
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
