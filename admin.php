<?php
include '../db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $date = $_POST['date'];
    $loc = $_POST['location'];
    $conn->query("INSERT INTO events(name, description, date, location) VALUES('$name','$desc','$date','$loc')");
    echo "Event added!";
}

echo "<h1>Manage Events</h1>";
echo "<form method='POST'>
        Name: <input type='text' name='name'><br>
        Description: <textarea name='description'></textarea><br>
        Date: <input type='date' name='date'><br>
        Location: <input type='text' name='location'><br>
        <button type='submit'>Add Event</button>
      </form>";
?>
