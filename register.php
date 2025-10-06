<?php
include 'db.php';
$email = $_POST['email'];
$password = $_POST['password'];
$sql = "INSERT INTO users (email, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $password);
if ($stmt->execute()) {
    echo "<p style='color:green; text-align:center;'>Registration successful! <a href='login.html'>Login Now</a></p>";
} else {
    echo "<p style='color:red; text-align:center;'>Email already exists. <a href='signup.html'>Try again</a></p>";
}
?>