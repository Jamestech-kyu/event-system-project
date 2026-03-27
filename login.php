<?php
session_start();
include __DIR__ . '/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");

    if ($result->num_rows == 0) {
        die("User not found.");
    }

    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];

        echo "✅ Login successful!<br>";
        echo "<a href='events.php'>Go to Events</a>";

    } else {
        echo "❌ Incorrect password.";
    }
}
?>

<h2>Login</h2>

<form method="POST">
    Email: <input type="email" name="email" required><br><br>
    Password: <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>
</form>