<?php
include 'db.php';
$attendee_id = 1; // Simulated logged-in user
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Attendee Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h1>Your Registrations</h1>
  <table class="table table-bordered">
    <thead><tr><th>Event</th><th>Ticket</th><th>Quantity</th><th>Payment Status</th></tr></thead>
    <tbody>
    <?php
    $result = $conn->query("SELECT r.registration_id, e.name, t.type, r.quantity, p.status
                            FROM registrations r
                            JOIN events e ON r.event_id=e.event_id
                            JOIN ticket_types t ON r.ticket_id=t.ticket_id
                            JOIN payments p ON r.registration_id=p.registration_id
                            WHERE r.attendee_id=$attendee_id");
    while($row = $result->fetch_assoc()) {
      echo "<tr>
              <td>{$row['name']}</td>
              <td>{$row['type']}</td>
              <td>{$row['quantity']}</td>
              <td>{$row['status']}</td>
            </tr>";
    }
    ?>
    </tbody>
  </table>
</div>
</body>
</html>
