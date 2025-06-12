/**
 * DataTables Basic
 */



"use strict";
 
var fv
document.addEventListener("DOMContentLoaded", function () {
    const formAddNewRecord = document.getElementById("addPermissionForm");

    if (formAddNewRecord) {
        fv = FormValidation.formValidation(formAddNewRecord, {
            fields: {
                'menu_id[]': { // Use 'menu_id[]' to validate multiple checkboxes
                    validators: {
                        callback: {
                            message: "Please select at least one menu",
                            callback: function (input) {
                                return formAddNewRecord.querySelectorAll('input[name="menu_id[]"]:checked').length > 0;
                            }
                        }
                    }
                }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: "",
                    rowSelector: ".form-check",
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus(),
            },
        });
    }
});

var dt_basic_table = $(".datatables-basic"),
    dt_basic;
    var urlParts = window.location.pathname.split('/');
    var userTypeId = urlParts[urlParts.length - 1];
// datatable (jquery)
$(function () {
    // DataTable with buttons
    // --------------------------------------------------------------------

    if (dt_basic_table.length) {
        dt_basic = dt_basic_table.DataTable({
            ordering: true,
            paging: false, 
            info: false, 
            lengthChange: false, 
            ajax: {
                url: "/user-access/" + userTypeId,
                dataSrc: function (json) {
                    json.data.forEach((element, index) => {
                        element.sequence_number = index + 1;
                    });
                    return json.data;
                },
                type: "GET",
                datatype: "json",
            },
            columns: [
                {
                    data: "sequence_number",
                },
                {
                    data: 'menu.name',
                    title: 'Menu',
                    render: function (data, type, row) {
                        return `<span style="padding-left: ${row.menu.level * 15}px">${data}</span>`;
                    }
                },           
                {
                    data: "view",
                    render: function (data, type, row) {
                      return data == 0
                        ? `
                    <label class="switch switch-primary">
                    <input type="checkbox" class="switch-input" data-id="${row.id}" name="view">
                    <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                    </span>
                    </label>`
                        : `
                    <label class="switch switch-primary">
                    <input type="checkbox" class="switch-input" data-id="${row.id}" checked name="view">
                    <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                    </span>
                    </label>`;
                    },
                  },
                {
                    data: "add",
                    render: function (data, type, row) {
                      return data == 0
                        ? `
                    <label class="switch switch-primary">
                    <input type="checkbox" class="switch-input" data-id="${row.id}" name="add">
                    <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                    </span>
                    </label>`
                        : `
                    <label class="switch switch-primary">
                    <input type="checkbox" class="switch-input" data-id="${row.id}" checked name="add">
                    <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                    </span>
                    </label>`;
                    },
                  },
                {
                    data: "edit",
                    render: function (data, type, row) {
                      return data == 0
                        ? `
                    <label class="switch switch-primary">
                    <input type="checkbox" class="switch-input" data-id="${row.id}" name="edit">
                    <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                    </span>
                    </label>`
                        : `
                    <label class="switch switch-primary">
                    <input type="checkbox" class="switch-input" data-id="${row.id}" checked name="edit">
                    <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                    </span>
                    </label>`;
                    },
                  },
                {
                    data: "delete",
                    render: function (data, type, row) {
                      return data == 0
                        ? `
                    <label class="switch switch-primary">
                    <input type="checkbox" class="switch-input" data-id="${row.id}" name="delete">
                    <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                    </span>
                    </label>`
                        : `
                    <label class="switch switch-primary">
                    <input type="checkbox" class="switch-input" data-id="${row.id}" checked name="delete">
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
                    // Actions
                    targets: 6,
                    title: "Actions",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        return (
                            '<div class="d-inline-block">' +
                            '<a href="javascript:;" class="dropdown-item text-danger delete-record" data-url = "' +
                            deleteUrl +
                            '"  data-id="' +
                            full.id +
                            '" ><i class="mdi mdi-delete"></i></a>' +
                            "</div>" 
                        );
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
                    text: '<i class="mdi mdi-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Permission</span>',
                    className:
                        "create-new btn btn-primary waves-effect waves-light",
                    action: function (e, dt, node, config) {
                        $("#addPermissionModal").modal("show");
                        $("#addPermissionForm").trigger("reset");
                    }
                },
            ],
            scrollX: true,
        });
        $("div.head-label").html('<h5 class="card-title mb-0">User Access</h5>');
    }

    //Submit Add form
    fv.on("core.form.valid", function () {
        var user_type_id = userTypeId;
        var url = $("#addPermissionForm").attr("action");
        var submitButton = $("button[type='submit']");
        $(".invalid-feedback").empty();
        var selectedMenus = $("input[name='menu_id[]']:checked").map(function () {
            return $(this).val();
        }).get();
        toggleButtonLoadingState(submitButton, true);
        $.ajax({
            url: url,
            type: "POST",
            data: {
               user_type_id : user_type_id,
               menu_id : selectedMenus
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.status == "success") {
                    toastr.success(response.message, "Success");
                    $("#addPermissionModal").modal("hide");
                    dt_basic.ajax.reload();
                    location.reload();
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

    $(".datatables-basic").on("click", 'input[type="checkbox"]', function () {

        var id = $(this).data("id");
        var permissionType = $(this).attr("name");
        var isChecked = $(this).is(":checked") ? 1 : 0;
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
  
        $.ajax({
          url: "/update-access-permission/" + id,
          type: "POST",
          data: {
            id: id,
            colName: permissionType,
            isChecked: isChecked,
          },
          headers: {
            "X-CSRF-TOKEN": csrfToken, 
          },
          success: function (response) {
            toastr.success(" Updated Successfully", "Success");
          },
          error: function (xhr, status, error) {
            toastr.error("Not updated. Try Again", "Error");
          },
        });
      });

      $(document).on('change', '.menu-checkbox', function () {
        var current = $(this);
        var currentId = current.data('id');
        var parentId = current.data('parent-id');
        var isChecked = current.is(':checked');
    
        checkAllChildren(currentId, isChecked);
    
        updateParent(parentId);
    });
    
    function checkAllChildren(parentId, isChecked) {
        var children = $('.menu-checkbox[data-parent-id="' + parentId + '"]');
        children.each(function () {
            $(this).prop('checked', isChecked);
            checkAllChildren($(this).data('id'), isChecked);
        });
    }
    
    function updateParent(childParentId) {
        if (!childParentId) return;
    
        var siblings = $('.menu-checkbox[data-parent-id="' + childParentId + '"]');
        var parent = $('.menu-checkbox[data-id="' + childParentId + '"]');
    
        if (siblings.filter(':checked').length > 0) {
            parent.prop('checked', true);
        } else {
            parent.prop('checked', false);
        }
    
        updateParent(parent.data('parent-id'));
    }
    
    
    
    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $(".dataTables_filter .form-control").removeClass("form-control-sm");
        $(".dataTables_length .form-select").removeClass("form-select-sm");
    }, 300);
});
