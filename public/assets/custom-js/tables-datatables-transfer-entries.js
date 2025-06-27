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
        let actionButtons = [];

        if (![1, 2, 3].includes(userRole)) {
            actionButtons = [
                {
                    text: '<i class="mdi mdi-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add Mannual Record</span>',
                    className:
                        "create-new btn btn-primary waves-effect waves-light d-none me-2",
                    action: function () {
                        window.location.href = manualTransferEntriesUrl;
                    },
                },
                {
                    text: '<i class="mdi mdi-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add Uploaded Record</span>',
                    className:
                        "create-new btn btn-label-primary waves-effect waves-light d-none",
                    action: function () {
                        window.location.href = uploadedTransferEntriesUrl;
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
                    data: "transfer_date",
                    name: "transfer_date",
                },
            ],
            columnDefs: [
                {
                    targets: 3,
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
                                '" target="_blank" class="btn btn-info btn-sm rounded-pill">View Uploaded</a>';
                        }

                        return buttons || "-";
                    },
                },
            ],
            // 👇 Modified layout to keep everything in a single row
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
            '<h5 class="card-title mb-0">Transfer Entries</h5>'
        );

        // 👇 Inject Flatpickr date range picker in the middle column
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
