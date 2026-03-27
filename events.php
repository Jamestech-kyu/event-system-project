<?php
include __DIR__ . '/db.php';

$result = $conn->query("SELECT * FROM events");

echo "<h2>Available Events</h2>";

if ($result->num_rows == 0) {
    echo "No events available.";
}

while($row = $result->fetch_assoc()){
    echo "<h3>".$row['title']."</h3>";
    echo "<p>".$row['description']."</p>";
    echo "<a href='event_details.php?id=".$row['id']."'>View Event</a><hr>";
}
?>