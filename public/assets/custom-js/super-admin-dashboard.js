/**
 *  Logistics Dashboard
 */

"use strict";

(function () {
    // Transfer booking Chart
    // --------------------------------------------------------------------

    let transferBookingOverview = new ApexCharts(
        document.querySelector("#transfer-booking-overview"),
        {
            chart: {
                type: "line",
                height: 350,
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: false,
                        zoom: false,
                        zoomin: false,
                        zoomout: false,
                        pan: false,
                        reset: false,
                    },
                },
            },
            series: [
                {
                    name: "Transferred Bookings",
                    data: bookingOverviewData.data,
                },
            ],
            colors: ["#826af9"],
            xaxis: {
                categories: bookingOverviewData.labels,
                labels: {
                    rotate: -45,
                },
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: "smooth",
                width: 3,
            },
            tooltip: {
                theme: "light",
            },
            legend: {
                position: "top",
            },
        }
    );

    transferBookingOverview.render();

    // Handle filter change
    function fetchTransferBookings() {
        const dateRange = $("#flatpickr-range").val().split(" to ");
        const startDate = dateRange[0] || "";
        const endDate = dateRange[1] || "";
        const hotelId = $("#hotel-filter").val();

        $.ajax({
            url: filteredGraphUrl,
            method: "GET",
            data: {
                start_date: startDate,
                end_date: endDate,
                hotel_id: hotelId,
            },
            success: function (response) {
                transferBookingOverview.updateOptions({
                    xaxis: { categories: response.labels },
                });
                transferBookingOverview.updateSeries([
                    {
                        name: "Transferred Bookings",
                        data: response.data,
                    },
                ]);
            },
        });
    }

    // Attach events
    $("#hotel-filter").on("change", fetchTransferBookings);

    flatpickr("#flatpickr-range", {
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: function () {
            fetchTransferBookings();
        },
    });
})();
