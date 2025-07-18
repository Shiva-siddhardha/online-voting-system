<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

// Handle approve/reject
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'] === 'approve' ? 'approved' : 'rejected';
    $stmt = $pdo->prepare('UPDATE candidates SET request_status = ? WHERE id = ?');
    $stmt->execute([$action, $id]);
    redirect('manage_candidates.php');
}

// Fetch candidate requests
$stmt = $pdo->query('SELECT c.*, u.name, u.email, e.title FROM candidates c JOIN users u ON c.user_id = u.id JOIN elections e ON c.election_id = e.id ORDER BY c.id DESC');
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Candidate Requests</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Manage Candidate Participation Requests</h2>
    <p><a href="../dashboard/admin.php">Back to Dashboard</a></p>
    <table border="1" cellpadding="5">
        <tr>
            <th>Election</th>
            <th>Candidate</th>
            <th>Email</th>
            <th>Party</th>
            <th>Photo</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($requests as $r): ?>
        <tr>
            <td><?= e($r['title']) ?></td>
            <td><?= e($r['name']) ?></td>
            <td><?= e($r['email']) ?></td>
            <td><?= e($r['party']) ?></td>
            <td><?php if ($r['photo']): ?><img src="../<?= e($r['photo']) ?>" width="50"><?php endif; ?></td>
            <td><?= e($r['request_status']) ?></td>
            <td>
                <?php if ($r['request_status'] === 'pending'): ?>
                    <a href="?action=approve&id=<?= $r['id'] ?>">Approve</a> |
                    <a href="?action=reject&id=<?= $r['id'] ?>">Reject</a>
                <?php else: ?>
                    <em><?= e(ucfirst($r['request_status'])) ?></em>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
