/**
 * DataTables Basic
 */

"use strict";

let offCanvasEl, fv;

document.addEventListener("DOMContentLoaded", function () {
    const formAddNewRecord = document.getElementById("menuForm");

    setTimeout(() => {
        const newRecord = document.querySelector(".create-new"),
            offCanvasElement = document.querySelector("#add-new-record");

        if (newRecord) {
            newRecord.addEventListener("click", function () {
                offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
                $(".form-control").removeClass("is-invalid");
                $(".invalid-feedback").empty();
                $("#menuForm").trigger("reset");

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
                name: {
                    validators: {
                        notEmpty: {
                            message: "Please enter menu name",
                        },
                    },
                },
                icon: {
                    validators: {
                        notEmpty: {
                            message: "Please enter icon class",
                        },
                    },
                },
                visible_at_web: {
                    validators: {
                        callback: {
                            message: "At least one visibility (web or mobile) must be selected",
                            callback: function (value, validator, $field) {
                                const web = $('[name="visible_at_web"]').is(':checked');
                                const app = $('[name="visible_at_app"]').is(':checked');
                                return web || app;
                            },
                        },
                    },
                },
                visible_at_app: {
                    validators: {
                        callback: {
                            message: "At least one visibility (web or mobile) must be selected",
                            callback: function (value, validator, $field) {
                                const web = $('[name="visible_at_web"]').is(':checked');
                                const app = $('[name="visible_at_app"]').is(':checked');
                                return web || app;
                            },
                        },
                    },
                },
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: "",
                    rowSelector: ".form-floating, .form-check",
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus(),
            },
        });

        // Watch for checkbox changes and revalidate both fields
        $('[name="visible_at_web"], [name="visible_at_app"]').on('change', function () {
            fv.revalidateField('visible_at_web');
            fv.revalidateField('visible_at_app');
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
            rowReorder: {
                selector: '.drag-handle'
            },
            ajax: {
                url: menuUrl,
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
                    className: 'drag-handle',
                    orderable: false,
                    searchable: false,
                    defaultContent: '<span style="cursor: move;">&#9776;</span>'
                },
                {
                    data: "sequence_number",
                },
                { data: "name" },
                {
                    data: "status",
                    render: function (data, type, row) {
                        return data == 0
                            ? `
                            <label class="switch switch-primary">
                            <input type="checkbox" class="switch-input status_${row.id}" onclick="changeStatus(${row.id})" data-id="${row.id}" data-url="${changeStatusURl}" name="status">
                            <span class="switch-toggle-slider">
                            <span class="switch-on"></span>
                            <span class="switch-off"></span>
                            </span>
                            </label>`
                            : `
                            <label class="switch switch-primary">
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
                {
                    // Sub Menu
                    targets: 4,
                    title: "Sub Menu",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        return (
                            '<a href="/sub-menus/' + btoa(full.id) + '" class=" rounded-pill btn btn-sm btn-primary submenu-record" data-id="' +
                            full.id +
                            '" >Sub Menu</a>' 
                        );
                    },
                },
                {
                    // Actions
                    targets: 5,
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
                         var editBtn = full.canEdit
                            ? '<a href="javascript:;" data-id="' +
                            full.id +
                            '" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-record"><i class="mdi mdi-pencil-outline"></i></a>'
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
    
                $(row).find("td:eq(1)").html(sequenceNumber);
            },
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
    
        $("div.head-label").html('<h5 class="card-title mb-0">Menus</h5>');
    
        dt_basic.on("row-reorder", function (e, diff, edit) {
            console.log("Row Reorder Event Fired", diff);
        
            var order = [];
            
            diff.forEach((row) => {
                var rowData = dt_basic.row(row.node).data();
                console.log("Row Data:", rowData);
        
                order.push({
                    id: rowData.id,
                    new_position: row.newPosition + 1 
                });
            });
        
            console.log("Final Order Payload:", order); 
        
            $.ajax({
                url: "/update-menu-order",
                type: "POST",
                data: JSON.stringify(order),
                contentType: "application/json",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    if (response.status == "success") {
                        toastr.success(response.message, "Success");
                        dt_basic.ajax.reload();
                    }
                },
                error: function (error) {
                    toastr.error("Failed to update menu order", "Error");
                }
            });
        });
        
        
        
    }

    //Submit Add form
    fv.on("core.form.valid", function () {
        var name = $("#name").val();
        var icon = $("#icon").val();
        var visible_at_web = $("#visible_at_web").is(":checked") ? 1 : 0;
        var visible_at_app = $("#visible_at_app").is(":checked") ? 1 : 0;        
        var url = $("#menuForm").attr("action");
        var submitButton = $("button[type='submit']");
        $(".invalid-feedback").empty();

        toggleButtonLoadingState(submitButton, true);

        $.ajax({
            url: url,
            type: "POST",
            data: {
                name: name,
                parent_id : parent_id,
                icon: icon,
                visible_at_web: visible_at_web,
                visible_at_app: visible_at_app
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

    const offCanvasElementEdit = document.querySelector("#menuEdit");
    const offCanvasElEdit = new bootstrap.Offcanvas(offCanvasElementEdit);

    $(".datatables-basic tbody").on("click", ".edit-record", function () {
        const id = $(this).data("id");
        if (id) {
            offCanvasElEdit.show();
            $(".form-control").removeClass("is-invalid");
            $(".invalid-feedback").empty();

            $.ajax({
                url: "/edit-menu/" + id,
                type: "GET",
                dataType: "json",
                success: function (response) {
                    $("#edit_name").val(response.data.name);
                    $("#edit_id").val(response.data.id);
                    $('#edit_icon').val(response.data.icon);
                    $('#edit_visible_at_web').prop('checked', response.data.visible_at_web == 1);
                    $('#edit_visible_at_app').prop('checked', response.data.visible_at_app == 1);
                },
                error: function (xhr) {
                    toastr.error("Something went wrong", "Error");
                },
            });
        }
    });

    //Submit edit form

    const formEditRecord = document.getElementById("menuEditForm");
    let fve;

    // Initialize form validation only once

    fve = FormValidation.formValidation(formEditRecord, {
        fields: {
            name: {
                validators: {
                    notEmpty: {
                        message: "Please enter menu name",
                    },
                },
            },
            icon: {
                validators: {
                    notEmpty: {
                        message: "Please enter menu icon",
                    },
                },
            },
            edit_visible_at_web: {
                validators: {
                    callback: {
                        message: "At least one visibility (web or mobile) must be selected",
                        callback: function (value, validator, $field) {
                            const web = $('[name="edit_visible_at_web"]').is(':checked');
                            const app = $('[name="edit_visible_at_app"]').is(':checked');
                            return web || app;
                        },
                    },
                },
            },
            edit_visible_at_app: {
                validators: {
                    callback: {
                        message: "At least one visibility (web or mobile) must be selected",
                        callback: function (value, validator, $field) {
                            const web = $('[name="edit_visible_at_web"]').is(':checked');
                            const app = $('[name="edit_visible_at_app"]').is(':checked');
                            return web || app;
                        },
                    },
                },
            },
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleValidClass: "",
                rowSelector: ".form-floating, .form-check",
            }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
    });

    // Watch for checkbox changes and revalidate both fields
    $('[name="edit_visible_at_web"], [name="edit_visible_at_app"]').on('change', function () {
        fve.revalidateField('edit_visible_at_web');
        fve.revalidateField('edit_visible_at_app');
    });

    fve.on("core.form.valid", function () {
        var id = $("#edit_id").val();
        var name = $("#edit_name").val();
        var icon = $("#edit_icon").val();
        var visible_at_web = $("#edit_visible_at_web").is(":checked") ? 1 : 0;
        var visible_at_app = $("#edit_visible_at_app").is(":checked") ? 1 : 0;
        var url = $("#menuEditForm").attr("action");
        var submitButton = $("button[type='submit']");
        toggleButtonLoadingState(submitButton, true);

        $.ajax({
            url: url,
            type: "PUT",
            data: {
                id: id,
                name: name,
                icon: icon,
                visible_at_web: visible_at_web,
                visible_at_app: visible_at_app
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
