<?php
session_start();


require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if session data is set
if (isset($_SESSION['someField']) && isset($_SESSION['uploads'])) {
    $someField = htmlspecialchars($_SESSION['someField']);
    $uploads = $_SESSION['uploads'];  // Array of file names
    $uploadFolder = 'uploads/'; // Folder where files are stored

    // Convert the array of uploaded files to a comma-separated string for database storage
    $uploadsString = implode(",", $uploads);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO newsletter (data, attachments) VALUES (?, ?)");
    $stmt->bind_param("ss", $someField, $uploadsString);

    if ($stmt->execute()) {
        echo "Data inserted successfully.";

        // Send email with PHPMailer
        $mail = new PHPMailer(true);

        try {
            // SMTP server configuration
            $mail->isSMTP();
            $mail->Host       = 'smtp.example.com'; // Your SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your_email@example.com';
            $mail->Password   = 'your_password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Email content
            $mail->setFrom('your_email@example.com', 'Your Name');
            $mail->addAddress('recipient@example.com', 'Recipient Name');
            $mail->isHTML(true);
            $mail->Subject = 'Data from Previous Page with Attachments';
            $mail->Body    = 'Here is the data from the previous page: ' . $someField;

            // Attach files from the upload folder
            foreach ($uploads as $file) {
                $filePath = $uploadFolder . $file;
                if (file_exists($filePath)) {
                    $mail->addAttachment($filePath); // Add each file as attachment
                }
            }

            // Send the email
            if ($mail->send()) {
                echo 'Email sent successfully with attachments.';
            } else {
                echo 'Email could not be sent.';
            }
        } catch (Exception $e) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();

    // Clear session variables if not needed anymore
    unset($_SESSION['someField']);
    unset($_SESSION['uploads']);
} else {
    echo "No data available.";
}

$conn->close();
?>
