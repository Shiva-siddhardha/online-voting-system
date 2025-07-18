<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

$election_id = isset($_GET['election_id']) ? (int)$_GET['election_id'] : 0;
if (!$election_id) {
    die('Invalid election ID.');
}
$stmt = $pdo->prepare('SELECT * FROM elections WHERE id = ?');
$stmt->execute([$election_id]);
$election = $stmt->fetch();
if (!$election) {
    die('Election not found.');
}
if ($election['status'] !== 'closed') {
    die('Results are only available after the election has ended.');
}
// Fetch candidates and votes
$stmt2 = $pdo->prepare('SELECT c.*, u.name FROM candidates c JOIN users u ON c.user_id = u.id WHERE c.election_id = ? ORDER BY votes DESC');
$stmt2->execute([$election_id]);
$candidates = $stmt2->fetchAll();
$winner = null;
$max_votes = null;
if ($candidates && $candidates[0]['votes'] > 0) {
    $max_votes = $candidates[0]['votes'];
    $winners = array_filter($candidates, function($c) use ($max_votes) { return $c['votes'] == $max_votes; });
} else {
    $winners = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Results - <?= e($election['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Results: <?= e($election['title']) ?></h2>
    <p><?= e($election['description']) ?></p>
    <?php if ($winners): ?>
        <h3 style="color:green;">Winner<?= count($winners) > 1 ? 's' : '' ?>:</h3>
        <ul>
        <?php foreach ($winners as $w): ?>
            <li><b><?= e($w['name']) ?></b> (<a href="candidate_profile.php?id=<?= $w['user_id'] ?>" target="_blank">View Profile</a>) with <?= e($w['votes']) ?> vote<?= $w['votes'] == 1 ? '' : 's' ?></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <h3>Candidates & Votes</h3>
    <?php if ($candidates): ?>
<div class="candidate-cards">
<?php foreach ($candidates as $c): ?>
    <div class="candidate-card<?= (isset($winners[$c['id']]) || (isset($winners) && in_array($c, $winners, true))) ? ' winner-card' : '' ?>">
        <div class="candidate-photo">
            <?php if ($c['photo']): ?>
                <img src="../<?= e($c['photo']) ?>" width="90">
            <?php endif; ?>
        </div>
        <div class="candidate-info">
            <div class="candidate-name"><b><?= e($c['name']) ?></b></div>
            <div class="candidate-party"><b>Party:</b> <?= e($c['party']) ?></div>
            <div class="candidate-votes"><b>Votes:</b> <?= e($c['votes']) ?></div>
            <a href="candidate_profile.php?id=<?= $c['user_id'] ?>" target="_blank" class="profile-link">View Profile</a>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php else: ?>
    <p>No candidates for this election.</p>
<?php endif; ?>
</body>
</html> 