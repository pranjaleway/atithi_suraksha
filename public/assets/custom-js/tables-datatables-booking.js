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
                { data: "guest_name", name: "guest_name" },
                { data: "aadhar_number", name: "aadhar_number" },
                { data: "contact_number", name: "contact_number" },
                { data: "room_number", name: "room_number" },
                {
                data: "created_at",
                name: "created_at",
                render: function (data, type, row) {
                        if (!data) return "-";

                        // Format using JavaScript's Date object
                        const dateObj = new Date(data);
                        const options = {
                            year: "numeric",
                            month: "short",
                            day: "numeric",
                            hour: "2-digit",
                            minute: "2-digit",
                            hour12: true,
                        };

                        return dateObj.toLocaleString("en-IN", options); // or "en-US" if preferred
                    }
                },
                { data:'parent_id',
                    render : function(data, type, row) {
                         var encodedId = btoa(row.id); // row.id = hotel_id
                        var url = membersUrl.replace(":id", encodedId);
                        return '<a href="'+ url  +'" class="btn btn-primary btn-sm rounded-pill">Members</a>';
                    }
                },
            ],
            columnDefs: [
                {
                    // Actions
                    targets: 7,
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
                        // var editBtn = full.canEdit
                        //     ? '<a href="edit-booking-details/'+ id +'" data-id="' +
                        //       full.id +
                        //       '" class="btn btn-sm btn-text-secondary rounded-pill btn-icon edit-record"><i class="mdi mdi-pencil-outline"></i></a>'
                        //     : "";

                        var viewBtn =
                            '<a href="view-booking-details/'+ id +'" data-id="' +
                            full.id +
                            '" class="btn btn-sm btn-text-secondary rounded-pill btn-icon view-record"><i class="mdi mdi-eye-outline"></i></a>';

                        if (deleteBtn == "") {
                            return viewBtn;
                        } else {
                            return viewBtn + deleteBtn;
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
                        window.location.href = "add-booking";
                    },
                },
            ],
            scrollX: true,
        });

        $("div.head-label").html('<h5 class="card-title mb-0">Bookings</h5>');
    }

    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $(".dataTables_filter .form-control").removeClass("form-control-sm");
        $(".dataTables_length .form-select").removeClass("form-select-sm");
    }, 300);
});
