<script>
    window.addEventListener("load", function() {
        setTimeout(function() {
            document.getElementById("loader-overlay").style.display = "none";
        }, 1000);
    });

    function toggleButtonLoadingState(button, isLoading) {
        if (isLoading) {
            // Disable the button and show the spinner
            button.prop("disabled", true);
            button.html(`
            <div class="spinner-border spinner-border-sm text-dark" role="status"></div>
        `);
        } else {
            // Enable the button and restore the original text
            button.prop("disabled", false);
            button.html("Submit");
        }
    }
</script>
<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

<!-- endbuild -->

<!-- Vendors JS -->
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/toastr/toastr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/block-ui/block-ui.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('assets/js/main.js') }}"></script>
<script> 
$(document).on("click", ".mark-as-read", function () {
    var notificationId = $(this).data("id");
    var item = $(this);

    $.ajax({
        url: "/mark-as-read",
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"), 
            id: notificationId
        },
        success: function (response) {
            if (response.success) {
                if(response.redirect){
                    window.location.href = response.redirect;
                }
            }
        },
        error: function () {
            alert("Failed to mark as read.");
        }
    });
});

</script>

<!-- Page JS -->
@yield('scripts')
</body>

</html>
