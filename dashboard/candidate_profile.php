<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
require_role('candidate');

$user_id = $_SESSION['user_id'];
$msg = '';

// Fetch current profile
$stmt = $pdo->prepare('SELECT bio, manifesto, profile_image FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$profile = $stmt->fetch();
$bio = $profile['bio'] ?? '';
$manifesto = $profile['manifesto'] ?? '';
$profile_image = $profile['profile_image'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');
    $manifesto = trim($_POST['manifesto'] ?? '');
    $img_path = $profile_image;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $img_path = 'assets/uploads/profile_' . $user_id . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['profile_image']['tmp_name'], '../' . $img_path);
    }
    $stmt = $pdo->prepare('UPDATE users SET bio = ?, manifesto = ?, profile_image = ? WHERE id = ?');
    $stmt->execute([$bio, $manifesto, $img_path, $user_id]);
    $msg = 'Profile updated successfully!';
    $profile_image = $img_path;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Candidate Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div style="float:right; margin-top:10px; margin-right:10px;">
        <?php if ($profile_image): ?><img src="../<?= e($profile_image) ?>" width="80" style="border-radius:50%;border:2px solid #2176ae;box-shadow:0 2px 8px rgba(44,62,80,0.10);background:#f0f4f8;"><?php endif; ?>
    </div>
    <h2>Edit Your Profile</h2>
    <p><a href="candidate.php">Back to Dashboard</a></p>
    <?php if ($msg): ?><div style="color:green;"> <?= e($msg) ?> </div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Bio:<br>
            <textarea name="bio" rows="4" cols="50" required><?= e($bio) ?></textarea>
        </label><br><br>
        <label>Manifesto:<br>
            <textarea name="manifesto" rows="6" cols="50" required><?= e($manifesto) ?></textarea>
        </label><br><br>
        <label>Profile Image:<br>
            <?php if ($profile_image): ?><img src="../<?= e($profile_image) ?>" width="100"><br><?php endif; ?>
            <input type="file" name="profile_image" accept="image/*">
        </label><br><br>
        <button type="submit">Save Changes</button>
    </form>
</body>
</html> 