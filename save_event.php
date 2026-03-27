<?php
include 'db.php';

$title = $_POST['title'];
$description = $_POST['description'];
$date = $_POST['date'];

$conn->query("INSERT INTO events (title, description, date, created_by)
VALUES ('$title', '$description', '$date', 1)");

echo "Event created successfully!<br>";
echo "<a href='events.php'>View Events</a>";
?>