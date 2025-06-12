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
                url: "/activity-log",
                data: function (d) {
                    d.date_range = $('#flatpickr-range').val(); 
                },
                dataSrc: function (json) {
                    json.data.forEach((element, index) => {
                        element.sequence_number = index + 1;
                        element.canDelete = json.canDelete || false;
                    });
                    return json.data;
                },
                type: "GET",
                datatype: "json",
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        return (
                            '<input type="checkbox" class="dt-checkboxes form-check-input select-entry" data-id="' +
                            full.id +
                            '">'
                        );
                    },
                    checkboxes: {
                        selectAllRender:
                            '<input type="checkbox" class="form-check-input" id="selectAllEntries">',
                    },
                },
                { data: "sequence_number" },
                { data: "activity" },
                { data: "date", width: "20%" },
                {
                    data: null,
                    title: "Actions",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        return full.canDelete
                            ? `<a href="javascript:;" class="text-danger delete-record" data-id="${full.id}"><i class="mdi mdi-delete"></i></a>`
                            : "Permission Denied";
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
            dom:
                '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>>' +
                '<"row"<"col-sm-12 col-md-6 d-flex align-items-center"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>' +
                "t" +
                '<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
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
            ],
            scrollX: true,
        });

        // Add delete button next to "Show entries"
        $(".dataTables_length").after(
            '<button class="btn btn-label-danger btn-sm ms-2 d-none" id="deleteSelected">Delete Selected</button>'
        );

        // Add date range picker before search input
        $(".dataTables_filter").addClass("d-flex align-items-center gap-2")
            .prepend(`
              <input type="text" class="form-control" placeholder="Select Date Range" id="flatpickr-range" />
          `);

        // Select All functionality
        $(document).on("change", "#selectAllEntries", function () {
            $(".select-entry").prop("checked", $(this).prop("checked"));
            toggleDeleteButton();
        });

        // Toggle delete button based on checkbox selection
        $(document).on("change", ".select-entry", function () {
            toggleDeleteButton();
        });

        function toggleDeleteButton() {
            $("#deleteSelected").toggleClass(
                "d-none",
                $(".select-entry:checked").length === 0
            );
        }

        // Handle bulk delete and single delete with one AJAX call
        function deleteRecords(selectedIds) {
            if (selectedIds.length === 0) return;

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
                customClass: {
                    confirmButton:
                        "btn btn-primary me-3 waves-effect waves-light",
                    cancelButton: "btn btn-outline-secondary waves-effect",
                },
                buttonsStyling: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        type: "DELETE",
                        data: {
                            ids: selectedIds,
                            _token: $('meta[name="csrf-token"]').attr(
                                "content"
                            ),
                        },
                        success: function (data) {
                            dt_basic.ajax.reload();
                            $("#deleteSelected").addClass("d-none");
                            toastr.success(data.message, "Success");
                        },
                    });
                }
            });
        }

        // Handle bulk delete
        $("#deleteSelected").on("click", function () {
            var selectedIds = $(".select-entry:checked")
                .map(function () {
                    return $(this).data("id");
                })
                .get();

            deleteRecords(selectedIds);
        });

        // Handle single delete
        $(document).on("click", ".delete-record", function () {
            var recordId = $(this).data("id");
            deleteRecords([recordId]);
        });

        const flatpickrRange = document.querySelector("#flatpickr-range");

       

        // Range
        if (typeof flatpickrRange != undefined) {
            flatpickrRange.flatpickr({
                mode: "range",
                dateFormat: "Y-m-d",
                onChange: function () {
                    dt_basic.ajax.reload();
                },
            });
        }

        $("div.head-label").html(
            '<h5 class="card-title mb-0">Activity Log</h5>'
        );
    }

    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $(".dataTables_filter .form-control").removeClass("form-control-sm");
        $(".dataTables_length .form-select").removeClass("form-select-sm");
    }, 300);
});
