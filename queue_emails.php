<?php
$data = json_decode($_POST['customers'], true);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

foreach ($data as $customer) {
    $stmt = $conn->prepare("INSERT INTO email_queue (cust_id, email) VALUES (?, ?)");
    $stmt->bind_param("is", $customer['id'], $customer['email']);
    $stmt->execute();
}

$conn->close();
echo "Emails queued successfully.";
?>
