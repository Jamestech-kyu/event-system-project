<?php
include __DIR__ . '/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        die("Email already registered.");
    }

    $conn->query("INSERT INTO users (name, email, password)
                  VALUES ('$name', '$email', '$password')");

    echo "✅ User registered successfully! <br>";
    echo "<a href='login.php'>Login here</a>";
}
?>

<h2>Sign Up</h2>

<form method="POST">
    Name: <input type="text" name="name" required><br><br>
    Email: <input type="email" name="email" required><br><br>
    Password: <input type="password" name="password" required><br><br>

    <button type="submit">Sign Up</button>
</form>