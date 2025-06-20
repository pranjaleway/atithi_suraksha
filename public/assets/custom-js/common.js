function changeStatus(id) {
    var url = $(".status_" + id).data("url");

    $.ajax({
        url: url,
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            id: id,
        },
        success: function (response) {
            if ((response.status = "success")) {
                toastr.success(response.message, "Success");
                dt_basic.ajax.reload();
            } else if (response == 0) {
                toastr.error(response.message, "Error");
            }
        },
        error: function (xhr) {
            toastr.error("An error occurred while changing the status.");
        },
    });
}

$(document).on("click", ".delete-record", function () {
    let deleteUrl = $(this).data("url");
    let id = $(this).data("id");

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        customClass: {
            confirmButton: "btn btn-primary me-3 waves-effect waves-light",
            cancelButton: "btn btn-outline-secondary waves-effect",
        },
        buttonsStyling: false,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: deleteUrl,
                type: "DELETE",
                data: {
                    id: id,
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    if (response.status === "success") {
                        toastr.success(response.message, "Success");
                        dt_basic.ajax.reload();
                    } else {
                        toastr.error("Something went wrong", "Error");
                    }
                },
                error: function () {
                    toastr.error("Something went wrong", "Error");
                },
            });
        }
    });
});

$(document).on("change", "#state_id", function () {
    var stateId = $(this).val();
    if (stateId) {
        $.ajax({
            url: cityUrl,
            type: "GET",
            data: { state_id: stateId },
            dataType: "json",
            success: function (response) {
                $("#city_id")
                    .empty()
                    .append('<option value="">Select City</option>');

                $.each(response.data, function (index, city) {
                    $("#city_id").append(
                        '<option value="' +
                            city.id +
                            '">' +
                            city.name +
                            "</option>"
                    );
                });
            },
            error: function () {
                $("#city_id")
                    .empty()
                    .append("<option>Error loading cities</option>");
            },
        });
    } else {
        $("#city_id").empty().append('<option value="">Select City</option>');
    }
});

$(document).ready(function () {
    const $inputs = $(".document-input");
    const $previewContainer = $("#all-preview-row");

    $inputs.on("change", function () {
        const file = this.files[0];
        const label = $(this).data("label");
        const id = this.id;
        const previewId = "preview_" + id;

        // Remove existing preview if file is reselected
        $("#" + previewId).remove();

        if (file) {
            const $col = $("<div>", {
                class: "col-md-4 mb-3",
                id: previewId,
            });

            const $title = $("<p>", {
                text: label,
                class: "fw-bold",
            });

            const fileType = file.type;
            const fileURL = URL.createObjectURL(file);
            let $previewEl;

            if (fileType.startsWith("image/")) {
                $previewEl = $("<img>", {
                    src: fileURL,
                    alt: label,
                    css: {
                        maxWidth: "100%",
                        maxHeight: "200px",
                    },
                });
            } else if (fileType === "application/pdf") {
                $previewEl = $("<iframe>", {
                    src: fileURL,
                    width: "100%",
                    height: "200px",
                    css: {
                        border: "1px solid #ccc",
                    },
                });
            } else {
                $previewEl = $("<p>").text("Preview not available");
            }

            $col.append($title, $previewEl);
            $previewContainer.append($col);
        }
    });
});
