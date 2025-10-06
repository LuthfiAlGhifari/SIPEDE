<?php
session_start();
include 'db.php';
$user_id = $_SESSION['user_id'];
$file_id = $_GET['id'];
$sql = "SELECT filepath FROM files WHERE id=? AND user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $file = $result->fetch_assoc();
    unlink($file['filepath']);
    $del = $conn->prepare("DELETE FROM files WHERE id=?");
    $del->bind_param("i", $file_id);
    $del->execute();
}
header("Location: dashboard.php");
?>