<?php
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shopping Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h1>Your Cart</h1>
  <?php
  if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticket_id = $_POST['ticket_id'];
    $quantity = $_POST['quantity'];
    $_SESSION['cart'][] = ['ticket_id' => $ticket_id, 'quantity' => $quantity];
  }

  if(!empty($_SESSION['cart'])) {
    echo "<table class='table table-striped'><thead><tr><th>Ticket ID</th><th>Quantity</th></tr></thead><tbody>";
    foreach($_SESSION['cart'] as $item) {
      echo "<tr><td>{$item['ticket_id']}</td><td>{$item['quantity']}</td></tr>";
    }
    echo "</tbody></table>";
    echo "<a href='checkout.php' class='btn btn-primary'>Proceed to Checkout</a>";
  } else {
    echo "<p class='alert alert-warning'>Cart is empty.</p>";
  }
  ?>
</div>
</body>
</html>
