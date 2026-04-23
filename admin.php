<?php
require_once __DIR__ . '/connect-db.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

$stmt = $db->query("SELECT UID, username, role FROM Users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Manage Users</h2>

<table border="1">
<tr>
    <th>Username</th>
    <th>Role</th>
    <th>Action</th>
</tr>

<?php foreach ($users as $u): ?>
<tr>
    <td><?php echo $u['username']; ?></td>
    <td><?php echo $u['role']; ?></td>
    <td>
        <?php if ($u['role'] !== 'admin'): ?>
            <form method="POST" action="promote.php">
                <input type="hidden" name="user_id" value="<?php echo $u['UID']; ?>">
                <button type="submit">Make Admin</button>
            </form>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
?>
