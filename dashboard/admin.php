<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

// Analytics queries
$total_voters = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'voter'")->fetchColumn();
$total_candidates = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'candidate'")->fetchColumn();
$total_admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$total_elections = $pdo->query("SELECT COUNT(*) FROM elections")->fetchColumn();
$total_votes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats { display: flex; gap: 2em; margin-bottom: 2em; }
        .stat-box { background: #f0f0f0; padding: 1em 2em; border-radius: 8px; text-align: center; }
        .stat-box h3 { margin: 0 0 0.5em 0; font-size: 1.2em; }
        .stat-box .num { font-size: 2em; font-weight: bold; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
    <h2>Admin Dashboard</h2>
    <p>Welcome, <?= e($_SESSION['name']) ?> (<a href="../logout.php">Logout</a>)</p>
    <div class="stats">
        <div class="stat-box"><h3>Voters</h3><div class="num"><?= $total_voters ?></div></div>
        <div class="stat-box"><h3>Candidates</h3><div class="num"><?= $total_candidates ?></div></div>
        <div class="stat-box"><h3>Admins</h3><div class="num"><?= $total_admins ?></div></div>
        <div class="stat-box"><h3>Elections</h3><div class="num"><?= $total_elections ?></div></div>
        <div class="stat-box"><h3>Votes Cast</h3><div class="num"><?= $total_votes ?></div></div>
    </div>
    <ul>
        <li><a href="../election/create.php">Create Election</a></li>
        <li><a href="../election/list.php">View All Elections</a></li>
        <li><a href="../admin/manage_candidates.php">Manage Candidate Requests</a></li>
        <li><a href="../admin/view_results.php">View Results</a></li>
        <li><a href="../admin/manage_users.php">Manage Users</a></li>
    </ul>
</body>
</html>
