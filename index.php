<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@10.16.6/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.16.6/dist/sweetalert2.all.min.js"></script>
    <style>
        /* Loader styling */
        #loader {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2em;
            color: #007bff;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Customer List</h2>
        
        <!-- Select All Checkbox with Count -->
        <div class="d-flex justify-content-between mb-3">
            <div>
                <input type="checkbox" id="selectAll"> Select All
            </div>
            <div id="selectedCount">0 Selected</div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-4" id="customerTable">
                <thead class="thead-dark">
                    <tr>
                        <th>Select</th>
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <button class="btn btn-primary mt-3" onclick="queueEmails()">Queue Selected Emails</button>
    </div>

    <!-- Loader Spinner -->
    <div id="loader">
        <span class="spinner-border spinner-border-lg" role="status" aria-hidden="true"></span> Processing...
    </div>

    <script>
        $(document).ready(function() {
            $.getJSON("fetch_customers.php", function(data) {
                $.each(data, function(index, customer) {
                    $("#customerTable tbody").append(
                        `<tr>
                            <td><input type="checkbox" class="customer-checkbox" data-id="${customer.ID}" data-email="${customer.Email}"></td>
                            <td>${customer.ID}</td>
                            <td>${customer.CustName}</td>
                            <td>${customer.Email}</td>
                        </tr>`
                    );
                });
            });

            // Handle Select All checkbox click
            $('#selectAll').click(function() {
                var isChecked = $(this).prop('checked');
                $(".customer-checkbox").prop('checked', isChecked);
                updateSelectedCount();
            });

            // Handle individual customer checkbox click
            $(document).on('click', '.customer-checkbox', function() {
                var allChecked = $(".customer-checkbox:checked").length === $(".customer-checkbox").length;
                $('#selectAll').prop('checked', allChecked);
                updateSelectedCount();
            });

            pollEmailQueue();
        });

        // Show loader for any AJAX request
        $(document).ajaxStart(function() {
            $('#loader').show();
        }).ajaxStop(function() {
            $('#loader').hide();
        });

        // Queue selected emails for batch processing
        function queueEmails() {
            let selectedCustomers = [];
            $(".customer-checkbox:checked").each(function() {
                selectedCustomers.push({
                    id: $(this).data("id"),
                    email: $(this).data("email")
                });
            });

            if (selectedCustomers.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Customers Selected',
                    text: 'Please select at least one customer to queue emails.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            $.ajax({
                url: "queue_emails.php",
                method: "POST",
                data: { customers: JSON.stringify(selectedCustomers) },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Emails Queued',
                        text: response,
                        confirmButtonText: 'OK'
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Queue Failed',
                        text: 'Failed to queue emails. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        // Poll the server every 5 minutes to process the email queue
        function pollEmailQueue() {
            setInterval(() => {
                $.ajax({
                    url: "process_email_queue.php",
                    method: "GET",
                    success: function(response) {
                        console.log("Batch processed: " + response);
                    },
                    error: function() {
                        console.error("Failed to process the email queue.");
                    }
                });
            }, 300000); // Poll every 5 minutes
        }

        // Update the selected count and display it
        function updateSelectedCount() {
            var selectedCount = $(".customer-checkbox:checked").length;
            $("#selectedCount").text(selectedCount + " Selected");
        }
    </script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
