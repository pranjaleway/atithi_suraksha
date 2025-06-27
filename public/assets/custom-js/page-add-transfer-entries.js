$('#add-form').on('submit', function(e) {
    e.preventDefault();

    var formAuthentication = this;
    var formdata = new FormData(this);
    var url = formAuthentication.getAttribute("action");
    var submitButton = $("button[type='submit']");
    $(".invalid-feedback").empty();

    toggleButtonLoadingState(submitButton, true);

    $.ajax({
        url: url,
        type: "POST",
        data: formdata,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(response) {
            if (response.status === "success") {
                toastr.success(response.message, "Success");
                formAuthentication.reset(); // Reset form
                window.location.href = response.redirect;
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $(".invalid-feedback").remove();
                $(".is-invalid").removeClass("is-invalid");

                let firstErrorElement = null;

                $.each(errors, function(key, value) {
                    let inputField = $("#" + key);
                    inputField.addClass("is-invalid");
                    inputField.after(`<div class="invalid-feedback">${value}</div>`);
                    if (!firstErrorElement) {
                        firstErrorElement = inputField;
                    }
                });

                if (firstErrorElement) {
                    $("html, body").animate({
                        scrollTop: firstErrorElement.offset().top - 100,
                    }, 200);
                }
            } else if (
                xhr.responseJSON &&
                xhr.responseJSON.message.includes("Duplicate entry")
            ) {
                let inputField = $("#email");
                inputField.addClass("is-invalid");
                inputField.after(`<div class="invalid-feedback">This email is already in use.</div>`);
                $("html, body").animate({
                    scrollTop: inputField.offset().top - 100,
                }, 200);
            } else {
                toastr.error("Something went wrong", "Error");
            }
        },
        complete: function() {
            toggleButtonLoadingState(submitButton, false);
        }
    });
});
