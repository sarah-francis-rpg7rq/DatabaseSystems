<?php
require_once __DIR__ . '/connect-db.php';


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit();
}

$username = $_POST['username'];
$password = $_POST['password'];

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO Users (username, password_hash) VALUES (:username, :password)";
$stmt = $db->prepare($sql);

$stmt->bindParam(':username', $username);
$stmt->bindParam(':password', $hashedPassword);

$stmt->execute();

echo "User registered successfully!";
?>