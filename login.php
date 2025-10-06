<?php
session_start();
include 'db.php';
$email = $_POST['email'];
$password = $_POST['password'];
$sql = "SELECT * FROM users WHERE email=? AND password=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    header("Location: dashboard.php");
} else {
    echo "<p style='color:red; text-align:center;'>Invalid login! Please Sign Up first.<br><a href='login.html'>Back to Login</a></p>";
}
?>