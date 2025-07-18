<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
require_role('candidate');

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Handle participation request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $election_id = $_POST['election_id'] ?? '';
    $party = trim($_POST['party'] ?? '');
    $photo = '';
    if ($election_id === '' || $party === '') {
        $error = 'Election and party are required.';
    } else {
        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo = 'assets/uploads/' . uniqid('cand_', true) . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../' . $photo);
        }
        try {
            $stmt = $pdo->prepare('INSERT INTO candidates (user_id, election_id, party, photo, request_status) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$user_id, $election_id, $party, $photo, 'pending']);
            $success = 'Participation request sent!';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'You have already requested participation for this election.';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Fetch open elections
$stmt = $pdo->prepare('SELECT * FROM elections WHERE status = ?');
$stmt->execute(['open']);
$elections = $stmt->fetchAll();

// Fetch candidate's requests
$stmt2 = $pdo->prepare('SELECT c.*, e.title FROM candidates c JOIN elections e ON c.election_id = e.id WHERE c.user_id = ? ORDER BY c.id DESC');
$stmt2->execute([$user_id]);
$requests = $stmt2->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Participate in Election</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Request to Participate in Election</h2>
    <p><a href="../dashboard/candidate.php">Back to Dashboard</a></p>
    <?php if ($error): ?><div style="color:red;"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div style="color:green;"><?= e($success) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Election:
            <select name="election_id" required>
                <option value="">Select</option>
                <?php foreach ($elections as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= e($e['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <label>Party Name: <input type="text" name="party" required></label><br>
        <label>Photo: <input type="file" name="photo" accept="image/*" required></label><br>
        <button type="submit">Send Request</button>
    </form>
    <h3>Your Participation Requests</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>Election</th>
            <th>Party</th>
            <th>Photo</th>
            <th>Status</th>
        </tr>
        <?php foreach ($requests as $r): ?>
        <tr>
            <td><?= e($r['title']) ?></td>
            <td><?= e($r['party']) ?></td>
            <td><?php if ($r['photo']): ?><img src="../<?= e($r['photo']) ?>" width="50"><?php endif; ?></td>
            <td><?= e($r['request_status']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
