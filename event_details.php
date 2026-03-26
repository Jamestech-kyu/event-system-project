<?php include 'db.php'; $event_id = $_GET['id']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Event Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <?php
  $event = $conn->query("SELECT * FROM events WHERE event_id=$event_id")->fetch_assoc();
  echo "<h1 class='mb-3'>{$event['name']}</h1><p>{$event['description']}</p>";
  ?>
  <h3 class="mt-4">Available Tickets</h3>
  <table class="table table-bordered">
    <thead><tr><th>Type</th><th>Price</th><th>Available</th><th>Action</th></tr></thead>
    <tbody>
    <?php
    $tickets = $conn->query("SELECT * FROM ticket_types WHERE event_id=$event_id");
    while($ticket = $tickets->fetch_assoc()) {
      echo "<tr>
              <td>{$ticket['type']}</td>
              <td>\${$ticket['price']}</td>
              <td>{$ticket['quantity']}</td>
              <td>
                <form method='POST' action='cart.php' class='d-flex'>
                  <input type='hidden' name='ticket_id' value='{$ticket['ticket_id']}'>
                  <input type='number' name='quantity' min='1' max='{$ticket['quantity']}' class='form-control me-2' style='width:80px'>
                  <button type='submit' class='btn btn-success'>Add</button>
                </form>
              </td>
            </tr>";
    }
    ?>
    </tbody>
  </table>
</div>
</body>
</html>
