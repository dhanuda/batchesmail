<?php
// Include PHPMailer files directly
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer;
use PHPMailer\Exception;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process emails in batches
$batch_size = 10;
$sql = "SELECT * FROM email_queue WHERE status = 'pending' LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $batch_size);
$stmt->execute();
$result = $stmt->get_result();

$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = 'smtp.example.com';
$mail->SMTPAuth = true;
$mail->Username = 'your_email@example.com';
$mail->Password = 'your_email_password';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

while ($email = $result->fetch_assoc()) {
    $mail->clearAddresses();
    $mail->addAddress($email['email']);
    $mail->setFrom('your_email@example.com', 'Your Name');
    $mail->Subject = 'Your Subject Here';
    $mail->Body = 'Hello, this is a test email.';

    if ($mail->send()) {
        // Mark email as processed
        $update_stmt = $conn->prepare("UPDATE email_queue SET status = 'processed', processed_at = NOW() WHERE queue_id = ?");
        $update_stmt->bind_param("i", $email['queue_id']);
        $update_stmt->execute();

        // Add to sent_emails table
        $sent_stmt = $conn->prepare("INSERT INTO sent_emails (queue_id, cust_id, email) VALUES (?, ?, ?)");
        $sent_stmt->bind_param("iis", $email['queue_id'], $email['cust_id'], $email['email']);
        $sent_stmt->execute();
    }
}

echo "Batch processed successfully.";
$conn->close();
?>
