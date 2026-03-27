<?php
include 'db.php';

// Fetch events
$events = $conn->query("SELECT id, title FROM events");
?>

<h2>Add Ticket</h2>

<form method="POST" action="save_ticket.php">

    Event:
    <select name="event_id" required>
        <?php while($e = $events->fetch_assoc()): ?>
            <option value="<?= $e['id'] ?>">
                <?= $e['title'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>

    Type: <input type="text" name="type" required><br><br>
    Price: <input type="number" name="price" required><br><br>
    Quantity: <input type="number" name="quantity" required><br><br>

    <button type="submit">Add Ticket</button>
</form>