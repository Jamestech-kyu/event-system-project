<?php
include __DIR__ . '/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];

$result = $conn->query("
    SELECT e.title, r.quantity, r.status 
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.user_id = $user_id
");

echo "<h2>My Registrations</h2>";

if ($result->num_rows == 0) {
    echo "No registrations yet.";
}

while($row = $result->fetch_assoc()){
    echo "<h3>".$row['title']."</h3>";
    echo "Tickets: ".$row['quantity']."<br>";
    echo "Status: ".$row['status']."<hr>";
}
?>