<?php
include __DIR__ . '/db.php';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$id = (int)$_GET['id'];

// Get event
$result = $conn->query("SELECT * FROM events WHERE id=$id");

if ($result->num_rows == 0) {
    die("Event not found.");
}

$event = $result->fetch_assoc();

// Get tickets
$tickets = $conn->query("SELECT * FROM tickets WHERE event_id=$id");

echo "<h2>".$event['title']."</h2>";
echo "<p>".$event['description']."</p>";

if ($tickets->num_rows == 0) {
    echo "No tickets available for this event.";
    exit;
}

echo "<form method='POST' action='register.php'>";

while($t = $tickets->fetch_assoc()){
    echo "<input type='radio' name='ticket_id' value='".$t['id']."' required>";
    echo $t['type']." - KES ".$t['price']." (Available: ".$t['quantity'].")<br>";
}

echo "<br>Quantity: <input type='number' name='qty' min='1' required><br><br>";
echo "<input type='hidden' name='event_id' value='".$id."'>";
echo "<button type='submit'>Register</button>";
echo "</form>";
?>