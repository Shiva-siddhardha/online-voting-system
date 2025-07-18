<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

// Handle block/unblock
if (isset($_GET['toggle'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $user = $pdo->prepare('SELECT status FROM users WHERE id = ?')->execute([$id]);
    $user = $pdo->query("SELECT status FROM users WHERE id = $id")->fetch();
    if ($user) {
        $newStatus = $user['status'] === 'blocked' ? 'approved' : 'blocked';
        $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $id]);
    }
    redirect('manage_users.php');
}

// Handle password reset
$reset_msg = '';
if (isset($_POST['reset_id'], $_POST['new_password'])) {
    $id = (int)$_POST['reset_id'];
    $new_password = $_POST['new_password'];
    if (strlen($new_password) < 6) {
        $reset_msg = 'Password must be at least 6 characters.';
    } else {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
        $reset_msg = 'Password reset successfully.';
    }
}

// Fetch all users
$users = $pdo->query('SELECT id, name, email, role, status FROM users ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .reset-form { display: inline; }
        .reset-input { width: 120px; }
    </style>
</head>
<body>
    <h2>Manage Users</h2>
    <p><a href="../dashboard/admin.php">Back to Dashboard</a></p>
    <?php if ($reset_msg): ?><div style="color:green;"> <?= e($reset_msg) ?> </div><?php endif; ?>
    <table border="1" cellpadding="5">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Block/Unblock</th>
            <th>Reset Password</th>
        </tr>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= e($u['name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['role']) ?></td>
            <td><?= e($u['status']) ?></td>
            <td>
                <?php if ($u['role'] !== 'admin'): ?>
                    <a href="?toggle=1&id=<?= $u['id'] ?>"><?= $u['status'] === 'blocked' ? 'Unblock' : 'Block' ?></a>
                <?php else: ?>
                    <em>N/A</em>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($u['role'] !== 'admin'): ?>
                <form method="post" class="reset-form">
                    <input type="hidden" name="reset_id" value="<?= $u['id'] ?>">
                    <input type="password" name="new_password" class="reset-input" placeholder="New password" required>
                    <button type="submit">Reset</button>
                </form>
                <?php else: ?>
                    <em>N/A</em>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html> 