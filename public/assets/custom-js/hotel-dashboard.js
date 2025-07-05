/**
 *  Logistics Dashboard
 */

"use strict";

(function () {
    // Transfer booking Chart
    // --------------------------------------------------------------------

    const transferBookingOverview = new ApexCharts(document.querySelector("#transfer-booking-overview"), {
    chart: {
        type: 'bar',
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
            }
        }
    },
    series: [
        {
            name: 'Total Bookings',
            data: monthlyData.dailyBookings
        },
        {
            name: 'Transferred Bookings',
            data: monthlyData.dailyTransfers
        }
    ],
    colors: ['#6c5ce7', '#00cec9'],
    xaxis: {
        categories: monthlyData.labels
    },
    plotOptions: {
        bar: {
            columnWidth: '30%',
            endingShape: 'rounded'
        }
    },
    dataLabels: { enabled: false },
    legend: { position: 'top' },
    tooltip: { theme: 'light' }
});

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
            // Update x-axis labels
            transferBookingOverview.updateOptions({
                xaxis: { categories: response.labels },
            });

            // Update series - handle both cases
            const series = [];

            if (response.dailyBookings && response.dailyTransfers) {
                // For user_type 4 & 5
                series.push(
                    { name: "Total Bookings", data: response.dailyBookings },
                    { name: "Transferred Bookings", data: response.dailyTransfers }
                );
            } else if (response.data) {
                // For user_type 1, 2, 3
                series.push(
                    { name: "Transferred Bookings", data: response.data }
                );
            }

            transferBookingOverview.updateSeries(series);
        },
    });
}

// Flatpickr initialization
flatpickr("#flatpickr-range", {
    mode: "range",
    dateFormat: "Y-m-d",
    onChange: function () {
        fetchTransferBookings();
    },
});


})();
