<?php
include __DIR__ . '/db.php';

$event_id = $_POST['event_id'];
$type = $_POST['type'];
$price = $_POST['price'];
$quantity = $_POST['quantity'];

$conn->query("INSERT INTO tickets (event_id, type, price, quantity)
VALUES ($event_id, '$type', $price, $quantity)");

echo "✅ Ticket added successfully!<br>";
echo "<a href='events.php'>Go to Events</a>";
?>