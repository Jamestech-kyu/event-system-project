<?php
include __DIR__ . '/db.php';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$reg_id = (int)$_GET['id'];

$conn->query("UPDATE registrations SET status='paid' WHERE id=$reg_id");

echo "<h2>Payment Successful (Simulated)</h2>";
echo "<a href='dashboard.php'>Go to Dashboard</a>";
?>