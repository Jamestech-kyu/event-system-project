<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/db.php';
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];

// Validate inputs
if (!isset($_POST['ticket_id'], $_POST['qty'], $_POST['event_id'])) {
    die("Invalid request.");
}

$ticket_id = (int)$_POST['ticket_id'];
$event_id  = (int)$_POST['event_id'];
$qty       = (int)$_POST['qty'];

if ($qty <= 0) {
    die("Quantity must be greater than 0.");
}

// Update tickets safely
$conn->query("UPDATE tickets 
              SET quantity = quantity - $qty 
              WHERE id = $ticket_id AND quantity >= $qty");

// Check if update worked
if ($conn->affected_rows == 0) {
    die("Not enough tickets available.");
}

// Insert registration
$conn->query("INSERT INTO registrations 
(user_id, event_id, ticket_id, quantity, status)
VALUES ($user_id, $event_id, $ticket_id, $qty, 'pending')");

$reg_id = $conn->insert_id;

echo "✅ Registration successful!<br>";
echo "<a href='checkout.php?id=$reg_id'>Proceed to Payment</a>";
?>