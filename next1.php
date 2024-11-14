<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Store form data in session
    $_SESSION['someField'] = $_POST['someField'];

    // Handle file uploads
    if (!empty($_FILES['attachments']['name'][0])) {
        $_SESSION['uploads'] = []; // Initialize uploads session array

        foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['attachments']['name'][$key];
            move_uploaded_file($tmp_name, "uploads/$file_name"); // Ensure the uploads directory exists
            $_SESSION['uploads'][] = $file_name; // Store uploaded file names in session
        }
    }

    // Respond with success
    echo json_encode(['success' => true]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/basic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center mb-4">Upload Form</h2>
            <form id="myForm" action="next1.php" method="POST" enctype="multipart/form-data" class="bg-light p-4 rounded shadow">
                <div class="mb-3">
                    <label for="someField" class="form-label">Enter some data</label>
                    <input type="text" name="someField" id="someField" class="form-control" placeholder="Enter some data" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload Files</label>
                    <div class="dropzone" id="myDropzone"></div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Next</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
Dropzone.options.myDropzone = {
    url: "next1.php", // Point to the same page for uploads
    autoProcessQueue: false,
    init: function() {
        var myDropzone = this;

        document.getElementById("myForm").onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            myDropzone.files.forEach(function(file) {
                formData.append("attachments[]", file);
            });

            fetch("next1.php", {
                method: "POST",
                body: formData
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      window.location.href = "next2.php"; // Redirect on success
                  }
              })
              .catch(error => console.error('Error:', error));
        };
    }
};
</script>

</body>
</html>
