<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents('php://input'), true);
$customerIds = $data['ids'] ?? [];

$batchSize = 10;
$customersToSend = array_slice($customerIds, 0, $batchSize);

$mailer = new PHPMailer(true);
$mailer->isSMTP();
$mailer->Host = 'smtp.example.com';
$mailer->SMTPAuth = true;
$mailer->Username = 'your_username';
$mailer->Password = 'your_password';
$mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mailer->Port = 587;

try {
    foreach ($customersToSend as $id) {
        $stmt = $pdo->prepare("SELECT email FROM email_queue WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        $mailer->setFrom('from@example.com', 'Your Name');
        $mailer->addAddress($customer['email']);
        $mailer->Subject = 'Your Subject Here';
        $mailer->Body = 'Your email body here';

        if ($mailer->send()) {
            $updateStmt = $pdo->prepare("UPDATE email_queue SET status = 'sent', last_sent_at = NOW() WHERE id = :id");
            $updateStmt->execute([':id' => $id]);
        }

        $mailer->clearAddresses();
    }

    echo json_encode(['message' => 'Emails processed successfully!']);
} catch (Exception $e) {
    echo json_encode(['message' => 'Error sending emails: ' . $mailer->ErrorInfo]);
}
?>
