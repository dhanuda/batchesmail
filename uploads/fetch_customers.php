<?php
// fetch_customers.php
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT id, name, email, status FROM email_queue");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($customers);
?>
