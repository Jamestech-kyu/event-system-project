<?php
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h1>Checkout</h1>
  <?php
  $conn->begin_transaction();
  try {
    foreach($_SESSION['cart'] as $item) {
      $ticket_id = $item['ticket_id'];
      $quantity = $item['quantity'];

      $check = $conn->query("SELECT quantity, event_id FROM ticket_types WHERE ticket_id=$ticket_id FOR UPDATE");
      $row = $check->fetch_assoc();

      if($row['quantity'] >= $quantity) {
        $conn->query("UPDATE ticket_types SET quantity=quantity-$quantity WHERE ticket_id=$ticket_id");
        $conn->query("INSERT INTO registrations(attendee_id,event_id,ticket_id,quantity) VALUES(1, {$row['event_id']}, $ticket_id, $quantity)");
        $registration_id = $conn->insert_id;
        $conn->query("INSERT INTO payments(registration_id, amount, status) VALUES($registration_id, 100.00, 'Completed')");
      } else {
        throw new Exception("Not enough tickets available");
      }
    }
    $conn->commit();
    echo "<div class='alert alert-success'>Checkout successful!</div>";
    $_SESSION['cart'] = [];
  } catch(Exception $e) {
    $conn->rollback();
    echo "<div class='alert alert-danger'>Checkout failed: " . $e->getMessage() . "</div>";
  }
  ?>
</div>
</body>
</html>
