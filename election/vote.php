<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
require_role('voter');

$user_id = $_SESSION['user_id'];
$election_id = $_GET['election_id'] ?? '';
$success = $error = '';

if ($election_id) {
    // Check if already voted
    $stmt = $pdo->prepare('SELECT * FROM votes WHERE user_id = ? AND election_id = ?');
    $stmt->execute([$user_id, $election_id]);
    $already_voted = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_voted) {
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

    // Fetch candidates for this election
    $stmt = $pdo->prepare('SELECT c.*, u.name, c.party, c.photo FROM candidates c JOIN users u ON c.user_id = u.id WHERE c.election_id = ? AND c.request_status = ?');
    $stmt->execute([$election_id, 'approved']);
    $candidates = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vote in Election</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Vote in Election</h2>
    <p><a href="../dashboard/voter.php">Back to Dashboard</a></p>
    <?php if (!$election_id): ?>
        <p>No election selected. <a href="list.php">View Elections</a></p>
    <?php else: ?>
        <?php if ($error): ?><div style="color:red;"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div style="color:green;"><?= e($success) ?></div><?php endif; ?>
        <?php if ($already_voted): ?>
            <div style="color:red;font-weight:bold;">You have already voted in this election. You cannot vote again.</div>
        <?php elseif ($candidates): ?>
            <form method="post">
                <table border="1" cellpadding="5">
                    <tr>
                        <th>Select</th>
                        <th>Candidate</th>
                        <th>Party</th>
                        <th>Photo</th>
                    </tr>
                    <?php foreach ($candidates as $c): ?>
                    <tr>
                        <td><input type="radio" name="candidate_id" value="<?= $c['id'] ?>" required></td>
                        <td><?= e($c['name']) ?> <a href="../public/candidate_profile.php?id=<?= $c['user_id'] ?>" target="_blank">View Profile</a></td>
                        <td><?= e($c['party']) ?></td>
                        <td><?php if ($c['photo']): ?><img src="../<?= e($c['photo']) ?>" width="50"><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <button type="submit">Vote</button>
            </form>
        <?php else: ?>
            <p>No approved candidates for this election.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
