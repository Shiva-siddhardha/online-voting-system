<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$role = $_SESSION['role'] ?? null;
$home = $role === 'admin' ? '/online-voting-system/dashboard/admin.php'
    : ($role === 'candidate' ? '/online-voting-system/dashboard/candidate.php'
    : ($role === 'voter' ? '/online-voting-system/dashboard/voter.php' : '/online-voting-system/index.php'));
$profile = $role === 'candidate' ? '/online-voting-system/dashboard/candidate_profile.php'
    : ($role === 'voter' ? '#' : '#'); // Add voter profile if needed
?>
<div class="main-header">
    <div class="header-left">
        <a href="<?= $home ?>" class="header-home">🏠 Home</a>
    </div>
    <div class="header-right">
        <?php if ($role === 'candidate'): ?>
            <a href="<?= $profile ?>" class="header-profile">Profile</a>
        <?php endif; ?>
        <a href="/online-voting-system/logout.php" class="header-logout">Logout</a>
    </div>
</div> 