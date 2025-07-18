<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
require_role('voter');

$user_id = $_SESSION['user_id'];
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
// Fetch candidates for this election
$stmt2 = $pdo->prepare('SELECT c.*, u.name, u.bio, u.manifesto, u.profile_image FROM candidates c JOIN users u ON c.user_id = u.id WHERE c.election_id = ? AND c.request_status = ?');
$stmt2->execute([$election_id, 'approved']);
$candidates = $stmt2->fetchAll();
// Check if voter has already voted
$stmt3 = $pdo->prepare('SELECT * FROM votes WHERE user_id = ? AND election_id = ?');
$stmt3->execute([$user_id, $election_id]);
$already_voted = $stmt3->fetch();
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_voted && $election['status'] === 'open') {
    $candidate_id = $_POST['candidate_id'] ?? '';
    if ($candidate_id) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO votes (user_id, election_id, candidate_id) VALUES (?, ?, ?)');
            $stmt->execute([$user_id, $election_id, $candidate_id]);
            $stmt = $pdo->prepare('UPDATE candidates SET votes = votes + 1 WHERE id = ?');
            $stmt->execute([$candidate_id]);
            $pdo->commit();
            $success = 'Your vote has been cast!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please select a candidate.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Election Details - <?= e($election['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Election: <?= e($election['title']) ?></h2>
    <p><?= e($election['description']) ?></p>
    <h3>Candidates</h3>
    <?php if ($candidates): ?>
    <form method="post">
    <div class="candidate-cards">
    <?php foreach ($candidates as $c): ?>
        <div class="candidate-card">
            <?php if (!$already_voted && $election['status'] === 'open'): ?>
                <input type="radio" name="candidate_id" value="<?= $c['id'] ?>" required>
            <?php endif; ?>
            <div class="candidate-photo">
                <?php if ($c['photo']): ?>
                    <img src="../<?= e($c['photo']) ?>" width="90">
                <?php elseif ($c['profile_image']): ?>
                    <img src="../<?= e($c['profile_image']) ?>" width="90">
                <?php endif; ?>
            </div>
            <div class="candidate-info">
                <div class="candidate-name"><b><?= e($c['name']) ?></b></div>
                <div class="candidate-bio"><b>Bio:</b> <?= nl2br(e($c['bio'])) ?></div>
                <div class="candidate-manifesto"><b>Manifesto:</b> <?= nl2br(e($c['manifesto'])) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <?php if ($error): ?><div style="color:red;"> <?= e($error) ?> </div><?php endif; ?>
    <?php if ($success): ?><div style="color:green;"> <?= e($success) ?> </div><?php endif; ?>
    <?php if (!$already_voted && $election['status'] === 'open'): ?>
        <button type="submit">Vote</button>
    <?php elseif ($already_voted): ?>
        <div style="color:red;font-weight:bold;">You have already voted in this election.</div>
    <?php elseif ($election['status'] !== 'open'): ?>
        <div style="color:orange;">Voting is closed for this election.</div>
    <?php endif; ?>
    </form>
    <?php else: ?>
        <p>No approved candidates for this election.</p>
    <?php endif; ?>
    <p><a href="list.php">Back to Elections</a></p>
</body>
</html> 