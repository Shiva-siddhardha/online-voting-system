<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_role('candidate');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidate Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Candidate Dashboard</h2>
    <p>Welcome, <?= e($_SESSION['name']) ?> (<a href="../logout.php">Logout</a>)</p>
    <ul>
        <li><a href="../dashboard/candidate_profile.php">Edit Profile</a></li>
        <li><a href="../election/list.php">View Open Elections</a></li>
        <li><a href="../election/participate.php">Request to Participate</a></li>
        <li><a href="../election/participate.php">View Participation Status</a></li>
    </ul>
</body>
</html>
