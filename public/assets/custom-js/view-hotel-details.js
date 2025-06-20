$(document).on("change", "#police_station_id", function () {
    var police_station_id = $(this).val();
    var hotel_id = $(this).data("id");
    var hotelUrl = $(this).data("url");
    if (police_station_id) {
        $.ajax({
            url: hotelUrl,
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: { police_station_id: police_station_id, hotel_id: hotel_id },
            dataType: "json",
            success: function (response) {
                if (response.status == "success") {
                    toastr.success(response.message, "Success");
                    location.reload();
                } else {
                    toastr.error("Something went wrong", "Error");
                }
            },
        });
    }
});
