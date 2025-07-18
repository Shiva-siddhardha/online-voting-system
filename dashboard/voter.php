<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
require_role('voter');

$user_id = $_SESSION['user_id'];
// Fetch voting history
$stmt = $pdo->prepare('SELECT v.election_id, v.created_at, e.title, c.user_id as candidate_user_id, u.name as candidate_name FROM votes v JOIN elections e ON v.election_id = e.id JOIN candidates c ON v.candidate_id = c.id JOIN users u ON c.user_id = u.id WHERE v.user_id = ? ORDER BY v.created_at DESC');
$stmt->execute([$user_id]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voter Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Voter Dashboard</h2>
    <p>Welcome, <?= e($_SESSION['name']) ?> (<a href="../logout.php">Logout</a>)</p>
    <ul>
        <li><a href="../election/list.php">View Elections</a></li>
    </ul>
    <h3>Your Voting History</h3>
    <?php if ($history): ?>
    <table border="1" cellpadding="5">
        <tr>
            <th>Election</th>
            <th>Voted For</th>
            <th>Date/Time</th>
        </tr>
        <?php foreach ($history as $h): ?>
        <tr>
            <td><?= e($h['title']) ?></td>
            <td><a href="../public/candidate_profile.php?id=<?= $h['candidate_user_id'] ?>" target="_blank"><?= e($h['candidate_name']) ?></a></td>
            <td><?= e($h['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p>You have not voted in any elections yet.</p>
    <?php endif; ?>
</body>
</html>
