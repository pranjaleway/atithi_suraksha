flatpickr("#flatpickr-range", {
    mode: "range",
    dateFormat: "Y-m-d",
});

("use strict");
$(document).ready(function () {
    var dt_basic_table = $(".datatables-basic"),
        dt_all_count = $(".all_count_table"),
        dt_hotel_count = $(".hotel_count_table");
    dt_basic_table.hide();
    dt_all_count.hide();
    dt_hotel_count.hide();

    $('input[name="reportType"]').change(function () {
        $("#countOptions").toggle($(this).val() === "count");
    });

    $(".reportForm")
        .off("submit")
        .on("submit", function (e) {
            e.preventDefault();
            var formData = $(this).serialize();
            [dt_basic_table, dt_all_count, dt_hotel_count].forEach(function (
                table
            ) {
                if ($.fn.DataTable.isDataTable(table)) {
                    table.DataTable().destroy();
                }
            });
            $.ajax({
                type: "POST",
                url: listUrl,
                data: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    var reportType = $(
                        'input[name="reportType"]:checked'
                    ).val();

                    if (reportType === "list") {
                        handleListReport(response);
                    } else if (reportType === "count") {
                        handleCountReport(response);
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    toastr.error(
                        "An error occurred while processing your request."
                    );
                },
            });
        });

    function handleListReport(data) {
        $(".all_count, .hotel_count, .no-data").addClass("d-none");

        $(".datatables-basic").prev(".text-center").remove();
        $(".datatables-basic").prev(".d-flex").remove();

        let dt_basic = dt_basic_table.DataTable({
            ordering: true,
            data: data,
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1; // Generate Serial Number
                    },
                },
                {
                    data: "hotel_name",
                    render: function (data, type, row) {
                        return data ? data : "N/A";
                    },
                },
                {
                    data: "total_transferred_bookings",
                },
                {
                    data: "today_transferred_bookings",
                },
            ],
        });

        $(".lists").removeClass("d-none");
        $(".all_count, .hotel_count").addClass("d-none");
        dt_basic_table.show();
    }

    function handleCountReport(data) {
        // Hide all existing tables and messages
        dt_basic_table.hide();
        $(".lists").addClass("d-none");
        $(".all_count, .hotel_count, .no-data").addClass("d-none");

        // Destroy previous tables if they exist
        [dt_all_count, dt_hotel_count].forEach(function (table) {
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().clear().destroy();
            }
        });

        // If backend returned nothing
        if (!data) {
            $(".no-data").removeClass("d-none");
            return;
        }

        // Convert backend object -> array with serial_number
        let formattedData = [
            {
                serial_number: 1,
                ...data,
            },
        ];

        console.log("Cleaned count data:", formattedData);

        // Decide which table to show
        if (data.hotel_count !== undefined) {
            initializeAllCountTable(formattedData);
        } else {
            initializeHotelTable(formattedData);
        }
    }

    function initializeAllCountTable(data) {
        dt_all_count.DataTable({
            ordering: true,
            data: data,
            columns: [
                { data: "serial_number" },
                { data: "hotel_count" },
                { data: "total_transferred_bookings" },
                { data: "today_transferred_bookings" },
            ],
        });

        toggleTables(".all_count", dt_all_count);
    }

    function initializeHotelTable(data) {
        dt_hotel_count.DataTable({
            data: data,
            columns: [
                { data: "serial_number" },
                { data: "total_transferred_bookings" },
                { data: "today_transferred_bookings" },
            ],
        });

        toggleTables(".hotel_count", dt_hotel_count);
    }

    function toggleTables(classSelector, table) {
        $(".lists, .all_count, .hotel_count").addClass("d-none");
        $(classSelector).removeClass("d-none");
        table.show();
    }
});
