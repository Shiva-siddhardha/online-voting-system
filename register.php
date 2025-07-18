<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$name = $email = $password = $role = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($name === '' || $email === '' || $password === '' || !in_array($role, ['voter', 'candidate'])) {
        $errors[] = 'All fields are required and role must be voter or candidate.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)');
            $status = 'approved';
            $stmt->execute([$name, $email, $hash, $role, $status]);
            $success = 'Registration successful! You can now <a href="login.php">login</a>.';
            $name = $email = $password = $role = '';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = 'Email already registered.';
            } else {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Online Voting System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Register</h2>
    <?php if ($errors): ?>
        <div style="color:red;">
            <?php foreach ($errors as $err) echo e($err) . '<br>'; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:green;">
            <?= $success ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <label>Name: <input type="text" name="name" value="<?= e($name) ?>" required></label><br>
        <label>Email: <input type="email" name="email" value="<?= e($email) ?>" required></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <label>Role:
            <select name="role" required>
                <option value="">Select</option>
                <option value="voter" <?= $role==='voter'?'selected':'' ?>>Voter</option>
                <option value="candidate" <?= $role==='candidate'?'selected':'' ?>>Candidate</option>
            </select>
        </label><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</body>
</html>
