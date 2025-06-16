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
                { data: "office_name", name: "office_name" },
                { data: "contact_number", name: "contact_number" },
                { data: "email", name: "email" },
                { data: "address", name: "address" },
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
                    // Actions
                    targets: 6,
                    title: "Actions",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        var id = btoa(full.id);
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
                            ? '<a href="edit-sp-office/' + id + '" data-id="' +
                              full.id +
                              '" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-record"><i class="mdi mdi-pencil-outline"></i></a>'
                            : "";

                        if (deleteBtn == "" && editBtn == "") {
                            return "Permission Denied";
                        } else {
                            return editBtn + deleteBtn;
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
                    window.location.href = "add-sp-office";
                },
                },
            ],
            scrollX: true,
        });

        $("div.head-label").html('<h5 class="card-title mb-0">SP Office</h5>');
    }


    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $(".dataTables_filter .form-control").removeClass("form-control-sm");
        $(".dataTables_length .form-select").removeClass("form-select-sm");
    }, 300);
});
