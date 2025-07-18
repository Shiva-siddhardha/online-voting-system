<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    die('Invalid candidate ID.');
}
// Fetch user profile
$stmt = $pdo->prepare('SELECT name, bio, manifesto, profile_image FROM users WHERE id = ? AND role = ?');
$stmt->execute([$id, 'candidate']);
$candidate = $stmt->fetch();
if (!$candidate) {
    die('Candidate not found.');
}
// Fetch all participation photos for this candidate
$stmt2 = $pdo->prepare('SELECT photo FROM candidates WHERE user_id = ? AND photo IS NOT NULL AND photo != ""');
$stmt2->execute([$id]);
$photos = $stmt2->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidate Profile - <?= e($candidate['name']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Candidate Profile: <?= e($candidate['name']) ?></h2>
    <?php if ($photos): ?>
        <?php foreach ($photos as $p): ?>
            <img src="../<?= e($p['photo']) ?>" width="150" style="margin:0 10px 10px 0;">
        <?php endforeach; ?>
    <?php elseif ($candidate['profile_image']): ?>
        <img src="../<?= e($candidate['profile_image']) ?>" width="150"><br><br>
    <?php endif; ?>
    <h3>Bio</h3>
    <p><?= nl2br(e($candidate['bio'])) ?></p>
    <h3>Manifesto</h3>
    <p><?= nl2br(e($candidate['manifesto'])) ?></p>
</body>
</html> 