

function changeStatus(id) {
    var url = $('.status_' + id).data('url');
    
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
            if (response.status = "success") {
                toastr.success(response.message, "Success");
                dt_basic.ajax.reload();
            } else if (response == 0) {
              toastr.error(response.message, "Error");
            }
        },
        error: function (xhr) {
            toastr.error(
                "An error occurred while changing the status."
            );
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
            cancelButton: "btn btn-outline-secondary waves-effect"
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: deleteUrl,
                type: "DELETE",
                data: {
                    id: id,
                    _token: $('meta[name="csrf-token"]').attr("content")
                },
                success: function (response) {
                    if (response.status === "success") {
                        toastr.success(response.message, "Success");
                        dt_basic.ajax.reload();
                    } else {
                        toastr.error('Something went wrong', "Error");
                    }
                },
                error: function () {
                    toastr.error('Something went wrong', "Error");
                }
            });
        }
    });
});
