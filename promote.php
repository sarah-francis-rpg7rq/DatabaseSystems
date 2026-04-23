<?php
session_start();
require_once __DIR__ . '/connect-db.php';

// Only admins can promote
if ($_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$user_id = $_POST['user_id'] ?? null;

if ($user_id) {
    $stmt = $db->prepare("UPDATE users SET role = 'admin' WHERE UID = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
}

header("Location: admin.php");
exit();
?>
