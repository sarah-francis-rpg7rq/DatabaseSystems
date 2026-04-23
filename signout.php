<?php
session_start();
$activeNav = '';
$pageTitle = 'Sign out';
require_once __DIR__ . '/header.php';
$_SESSION = [];
session_destroy();
?>
<p class="text-secondary">You are signed out. <a href="login.php" class="link-light">Log in</a> again to continue.</p>
<?php require_once __DIR__ . '/app-shell-end.php'; ?>
