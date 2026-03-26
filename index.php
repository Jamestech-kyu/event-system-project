<?php
include 'db.php';

$result = $conn->query("SELECT * FROM events");
echo "<h1>Event Calendar</h1>";

while($row = $result->fetch_assoc()) {
    echo "<h2>{$row['name']}</h2>";
    echo "<p>{$row['description']}</p>";
    echo "<p>Date: {$row['date']} | Location: {$row['location']}</p>";
    echo "<a href='event_details.php?id={$row['event_id']}'>View Details</a><hr>";
}
?>
