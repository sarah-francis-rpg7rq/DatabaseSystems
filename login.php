<?php
session_start();
require_once __DIR__ . '/connect-db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT UID, password_hash FROM Users WHERE username = :username";
$stmt = $db->prepare($sql);
$stmt->bindParam(':username', $username);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['user_id'];
    header("Location: dashboard.php");
    exit();
} else {
    echo "Invalid login";
}
?>