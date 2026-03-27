<?php
include 'db.php';
?>

<h2>Add New Event</h2>

<form method="POST" action="save_event.php">
    Title: <input type="text" name="title" required><br><br>
    Description: <textarea name="description" required></textarea><br><br>
    Date: <input type="datetime-local" name="date" required><br><br>
    
    <button type="submit">Create Event</button>
</form>