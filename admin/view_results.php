<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

// Fetch all elections
$stmt = $pdo->query('SELECT * FROM elections ORDER BY created_at DESC');
$elections = $stmt->fetchAll();

// Fetch results for each election
$results = [];
foreach ($elections as $election) {
    $stmt2 = $pdo->prepare('SELECT c.*, u.name FROM candidates c JOIN users u ON c.user_id = u.id WHERE c.election_id = ? ORDER BY votes DESC');
    $stmt2->execute([$election['id']]);
    $results[$election['id']] = $stmt2->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Results</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Election Results</h2>
    <p><a href="../dashboard/admin.php">Back to Dashboard</a></p>
    <?php foreach ($elections as $election): ?>
        <h3><?= e($election['title']) ?></h3>
        <p>Status: <?= e($election['status']) ?></p>
        <?php if (!empty($results[$election['id']])): ?>
            <table border="1" cellpadding="5">
                <tr>
                    <th>Candidate</th>
                    <th>Party</th>
                    <th>Votes</th>
                </tr>
                <?php foreach ($results[$election['id']] as $c): ?>
                <tr>
                    <td><?= e($c['name']) ?></td>
                    <td><?= e($c['party']) ?></td>
                    <td><?= e($c['votes']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No candidates for this election.</p>
        <?php endif; ?>
    <?php endforeach; ?>
</body>
</html>
