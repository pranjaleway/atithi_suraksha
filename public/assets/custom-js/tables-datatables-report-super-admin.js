flatpickr("#flatpickr-range", {
    mode: "range",
    dateFormat: "Y-m-d",
});

$(document).on("change", "#city_id", function () {
    var id = $(this).val();
    if (id) {
        $.ajax({
            url: policeStationByCityUrl,
            type: "GET",
            data: { id: id },
            dataType: "json",
            success: function (response) {
                $("#police_station_id")
                    .empty()
                    .append('<option value="">Select Police Station</option>');

                if (response.data && response.data.length > 0) {
                    $.each(response.data, function (index, city) {
                        $("#police_station_id").append(
                            '<option value="' +
                                city.id +
                                '">' +
                                city.police_station_name +
                                "</option>"
                        );
                    });
                } else {
                    $("#police_station_id").append(
                        '<option value="">No Police Station Available</option>'
                    );
                }
            },
            error: function () {
                $("#police_station_id")
                    .empty()
                    .append("<option>Error loading Police Stations</option>");
            },
        });
    } else {
        $("#police_station_id")
            .empty()
            .append('<option value="">Select Police Station</option>');
    }
});
$(document).on("change", "#police_station_id", function () {
    var id = $(this).val();
    if (id) {
        $.ajax({
            url: hotelsByPoliceStationURL,
            type: "GET",
            data: { police_station_id: id },
            dataType: "json",
            success: function (response) {
                $("#hotel_id")
                    .empty()
                    .append('<option value="">Select Hotel</option>');

                if (response.data && response.data.length > 0) {
                    $.each(response.data, function (index, hotel) {
                        $("#hotel_id").append(
                            '<option value="' +
                                hotel.id +
                                '">' +
                                hotel.hotel_name +
                                "</option>"
                        );
                    });
                } else {
                    $("#hotel_id").append(
                        '<option value="">No Hotels Available</option>'
                    );
                }
            },
            error: function () {
                $("#hotel_id")
                    .empty()
                    .append("<option>Error loading Hotels</option>");
            },
        });
    } else {
        $("#hotel_id").empty().append('<option value="">Select Hotel</option>');
    }
});

("use strict");
$(document).ready(function () {
    var dt_basic_table = $(".datatables-basic"),
        dt_all_count = $(".all_count_table"),
        dt_police_station_count = $(".police_station_count_table"),
        dt_hotel_count = $(".hotel_count_table");
    dt_basic_table.hide();
    dt_all_count.hide();
    dt_police_station_count.hide();
    dt_hotel_count.hide();

    $('input[name="reportType"]').change(function () {
        $("#countOptions").toggle($(this).val() === "count");
    });

    $(".reportForm")
        .off("submit")
        .on("submit", function (e) {
            e.preventDefault();
            var formData = $(this).serialize();
            [
                dt_basic_table,
                dt_all_count,
                dt_police_station_count,
                dt_hotel_count,
            ].forEach(function (table) {
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
        $(
            ".all_count, .police_station_count, .hotel_count,  .no-data"
        ).addClass("d-none");

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
                    data: "city_name",
                },
                {
                    data: "office_name",
                },
                {
                    data: "police_station_name",
                },
                {
                    data: "hotel_name",
                    render: function (data, type, row) {
                        if (data) {
                            return row.owner_name
                                ? `${data} (${row.owner_name})`
                                : data;
                        }
                        return "N/A";
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
        $(".all_count, .police_station_count, .hotel_count").addClass("d-none");
        dt_basic_table.show();
    }

    function handleCountReport(data) {
        if (data && typeof data === "object" && data.data !== undefined) {
            data = data.data;
        }

        dt_basic_table.hide();
        $(".lists").addClass("d-none");
        $(".all_count, .police_station_count, .hotel_count, .no-data").addClass(
            "d-none"
        );

        [dt_all_count, dt_police_station_count, dt_hotel_count].forEach(
            function (table) {
                if ($.fn.DataTable.isDataTable(table)) {
                    table.DataTable().clear().destroy();
                }
            }
        );

        let formattedData = Array.isArray(data) ? data : data ? [data] : [];

        if (
            !data ||
            (Array.isArray(data) && data.length === 0) || // []
            (typeof data === "object" && Object.keys(data).length === 0) // {}
        ) {
            $(".no-data").removeClass("d-none");
            return;
        }

        formattedData = formattedData.map((item, index) => ({
            serial_number: index + 1,
            ...item,
        }));

        let firstRow = formattedData[0];

        if (
            firstRow.city_name !== undefined &&
            firstRow.police_station_count !== undefined &&
            firstRow.hotel_count !== undefined
        ) {
            initializeAllCountTable(formattedData);
        } else if (firstRow.hotel_count !== undefined) {
            initializePoliceStationTable(formattedData);
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
                { data: "city_name" },
                { data: "police_station_count" },
                { data: "hotel_count" },
                { data: "total_transferred_bookings" },
                { data: "today_transferred_bookings" },
            ],
        });

        toggleTables(".all_count", dt_all_count);
    }

    function initializePoliceStationTable(data) {
        dt_police_station_count.DataTable({
            ordering: true,
            data: data,
            columns: [
                { data: "serial_number" },
                { data: "hotel_count" },
                { data: "total_transferred_bookings" },
                { data: "today_transferred_bookings" },
            ],
        });

        toggleTables(".police_station_count", dt_police_station_count);
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
        $(".lists, .all_count, .hotel_count, .police_station_count").addClass(
            "d-none"
        );
        $(classSelector).removeClass("d-none");
        table.show();
    }
});
