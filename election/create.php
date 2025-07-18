<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$title = $description = '';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($title === '') {
        $error = 'Title is required.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO elections (title, description, created_by, status) VALUES (?, ?, ?, ?)');
            $stmt->execute([$title, $description, $_SESSION['user_id'], 'open']);
            $success = 'Election created successfully!';
            $title = $description = '';
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Election</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Create Election</h2>
    <p><a href="../dashboard/admin.php">Back to Dashboard</a></p>
    <?php if ($error): ?><div style="color:red;"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div style="color:green;"><?= e($success) ?></div><?php endif; ?>
    <form method="post">
        <label>Title: <input type="text" name="title" value="<?= e($title) ?>" required></label><br>
        <label>Description:<br><textarea name="description" rows="4" cols="40"><?= e($description) ?></textarea></label><br>
        <button type="submit">Create Election</button>
    </form>
</body>
</html>
