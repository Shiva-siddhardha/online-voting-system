<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
require_login();

$role = $_SESSION['role'];

// Handle end election
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['end_election_id']) && $role === 'admin') {
    $eid = (int)$_POST['end_election_id'];
    $stmt = $pdo->prepare('UPDATE elections SET status = ? WHERE id = ?');
    $stmt->execute(['closed', $eid]);
    redirect('list.php');
}

if ($role === 'admin') {
    $stmt = $pdo->query('SELECT * FROM elections ORDER BY id DESC');
} else {
    $stmt = $pdo->query('SELECT * FROM elections ORDER BY id DESC');
}
$elections = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>List of Elections</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>List of Elections</h2>
    <p><a href="../dashboard/<?= e($role) ?>.php">Back to Dashboard</a></p>
    <table border="1" cellpadding="5">
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($elections as $e):
            $status = $e['status'];
        ?>
        <tr>
            <td><?= e($e['title']) ?></td>
            <td><?= e($e['description']) ?></td>
            <td><?= e($status) ?></td>
            <td>
                <?php if ($role === 'admin'): ?>
                    <?php if ($status === 'open'): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="end_election_id" value="<?= $e['id'] ?>">
                            <button type="submit" onclick="return confirm('End this election now?')">End Now</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($status === 'closed'): ?>
                        <a href="../public/results.php?election_id=<?= $e['id'] ?>" target="_blank">View Results</a>
                    <?php endif; ?>
                    <em>Admin</em>
                <?php elseif ($role === 'candidate'): ?>
                    <?php if ($status === 'open'): ?>
                        <a href="participate.php?election_id=<?= $e['id'] ?>">Participate</a>
                    <?php endif; ?>
                    <?php if ($status === 'closed'): ?>
                        <a href="../public/results.php?election_id=<?= $e['id'] ?>" target="_blank">View Results</a>
                    <?php endif; ?>
                <?php elseif ($role === 'voter'): ?>
                    <a href="view.php?election_id=<?= $e['id'] ?>">View</a>
                    <?php if ($status === 'closed'): ?>
                        | <a href="../public/results.php?election_id=<?= $e['id'] ?>" target="_blank">View Results</a>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
