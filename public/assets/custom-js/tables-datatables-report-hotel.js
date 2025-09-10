flatpickr("#flatpickr-range", {
    mode: "range",
    dateFormat: "Y-m-d",
});
("use strict");
$(document).ready(function () {
    var dt_basic_table = $(".datatables-basic"),
        dt_all_count = $(".all_count_table"),
        dt_manual_count = $(".manual_count_table");
    dt_uploaded_count = $(".uploaded_count_table");
    dt_basic_table.hide();
    dt_all_count.hide();
    dt_manual_count.hide();
    dt_uploaded_count.hide();

    $('input[name="reportType"]').change(function () {
        $("#countOptions").toggle($(this).val() === "count");
    });

    $(".reportForm")
        .off("submit")
        .on("submit", function (e) {
            e.preventDefault();

            var formData = $(this).serialize();
            var transferType = $("#transfer_type").val();

            // Destroy all DataTables if they exist
            [
                dt_basic_table,
                dt_all_count,
                dt_manual_count,
                dt_uploaded_count,
            ].forEach(function (table) {
                if ($.fn.DataTable.isDataTable(table)) {
                    $(table).DataTable().clear().destroy();
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
                        handleListReport(response, transferType);
                    } else if (reportType === "count") {
                        handleCountReport(response, transferType);
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

    function handleListReport(data, transferType) {
        $(".all_count, .manual_count, .no-data, .uploaded_count").addClass(
            "d-none"
        );

        // Destroy old DataTable if exists
        if ($.fn.DataTable.isDataTable("#listing_table")) {
            $("#listing_table").DataTable().clear().destroy();
        }

        // Normalize type
        let type = (transferType || "all").toLowerCase();

        // Build dynamic headers + columns
        let headers = ["S.No", "Date"];
        let columns = [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                },
            },
            {
                data: "transfer_date",
                render: function (data) {
                    if (!data) return "-";
                    let d = new Date(data);
                    let day = String(d.getDate()).padStart(2, "0");
                    let month = String(d.getMonth() + 1).padStart(2, "0");
                    let year = d.getFullYear();
                    return `${day}/${month}/${year}`;
                },
            },
        ];

        if (type === "all") {
            headers.push("Transfer Type", "Name");
            columns.push({ data: "transfer_type" });
            columns.push({
                data: "guest_name",
                render: function (data) {
                    return data ? data : "-";
                },
            });
        } else if (type === "manual") {
            headers.push("Name");
            columns.push({
                data: "guest_name",
                render: function (data) {
                    return data ? data : "-";
                },
            });
        }

        // Action column
        headers.push("Action");
        columns.push({
            data: null,
            render: function (row) {
                var id = btoa(row.booking_id);
                var viewRoute = viewDetailsUrl.replace(":id", id);
                if (row.transfer_type === "manual" && row.booking_id) {
                    return `<a href="${viewRoute}" target="_blank" class="btn btn-sm btn-text-secondary rounded-pill btn-icon view-record"><i class="mdi mdi-eye-outline"></i></a>`;
                } else if (row.transfer_type === "uploaded" && row.file_path) {
                    return `<a href="${uploadedUrl}${row.file_path}" target="_blank" class="btn btn-sm btn-text-secondary rounded-pill btn-icon"><i class="mdi mdi-eye-outline"></i></a>`;
                } else {
                    return "-";
                }
            },
        });

        // Rebuild <thead>
        let thead = $("#listing_table thead tr");
        thead.empty();
        headers.forEach(function (h) {
            thead.append(`<th>${h}</th>`);
        });

        // Initialize DataTable
        $("#listing_table").DataTable({
            ordering: true,
            data: data,
            columns: columns,
        });

        $(".lists").removeClass("d-none");
        dt_basic_table.show();
    }

    function handleCountReport(data, transferType) {
        // Hide all tables/messages
        dt_basic_table.hide();
        $(
            ".lists, .all_count, .manual_count, .uploaded_count, .no-data"
        ).addClass("d-none");

        // Destroy previous tables
        [dt_all_count, dt_manual_count, dt_uploaded_count].forEach(function (
            table
        ) {
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().clear().destroy();
            }
        });

        // If no data
        if (!data || data.length === 0) {
            $(".no-data").removeClass("d-none");
            return;
        }

        // Normalize transfer type
        var type = (transferType || "all").toLowerCase();

        // Initialize table based on type
        if (type === "all") {
            initializeAllCountTable(data);
        } else if (type === "manual") {
            initializeManualTable(data);
        } else if (type === "uploaded") {
            initializeUploadedTable(data);
        }
    }

    function initializeAllCountTable(data) {
        dt_all_count.DataTable({
            ordering: true,
            data: data,
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1; // Serial number
                    },
                },
                {
                    data: "transfer_date",
                    render: function (data) {
                        if (!data) return "-";
                        let d = new Date(data);
                        let day = String(d.getDate()).padStart(2, "0");
                        let month = String(d.getMonth() + 1).padStart(2, "0");
                        let year = d.getFullYear();
                        return `${day}/${month}/${year}`;
                    },
                },
                { data: "manual_count" },
                { data: "uploaded_count" },
            ],
        });

        toggleTables(".all_count", dt_all_count);
    }

    function initializeManualTable(data) {
        dt_manual_count.DataTable({
            ordering: true,
            data: data,
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    },
                },
                {
                    data: "transfer_date",
                    render: function (data) {
                        if (!data) return "-";
                        let d = new Date(data);
                        let day = String(d.getDate()).padStart(2, "0");
                        let month = String(d.getMonth() + 1).padStart(2, "0");
                        let year = d.getFullYear();
                        return `${day}/${month}/${year}`;
                    },
                },
                { data: "transfer_type" },
                { data: "total_count" }, // manual count
            ],
        });

        toggleTables(".manual_count", dt_manual_count);
    }

    function initializeUploadedTable(data) {
        dt_uploaded_count.DataTable({
            ordering: true,
            data: data,
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    },
                },
                {
                    data: "transfer_date",
                    render: function (data) {
                        if (!data) return "-";
                        let d = new Date(data);
                        let day = String(d.getDate()).padStart(2, "0");
                        let month = String(d.getMonth() + 1).padStart(2, "0");
                        let year = d.getFullYear();
                        return `${day}/${month}/${year}`;
                    },
                },
                { data: "transfer_type" },
                { data: "total_count" }, // uploaded count
            ],
        });

        toggleTables(".uploaded_count", dt_uploaded_count);
    }

    function toggleTables(classSelector, table) {
        $(".lists, .all_count, .manual_count, .uploaded_count").addClass(
            "d-none"
        );
        $(classSelector).removeClass("d-none");
        table.show();
    }
});
