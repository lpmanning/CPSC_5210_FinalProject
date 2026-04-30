<!-- same as other pages for login -->
<?php
session_start();
require_once 'db.php';
if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit(); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? 'student';

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Username or email already taken.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $email, $hashed, $role]);

            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            header('Location: dashboard.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — AP Cyber Field Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-single">

    <div class="auth-single-shapes">
        <img src="images/2.png" alt="">
        <img src="images/9.png" alt="">
        <img src="images/1.png" alt="">
        <img src="images/4.png" alt="">
    </div>

    <div class="auth-single-body">

        <div class="auth-single-headline">
            <span class="auth-headline-serif">AP Cyber</span>
            <span class="auth-headline-condensed">Field Guide</span>
        </div>

        <div class="auth-single-form">
            <p class="auth-form-eyebrow">Create Account</p>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>I am a...</label>
                    <select name="role">
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Create Account</button>
            </form>
            <p class="switch-link">Already have an account? <a href="index.php">Sign in here</a></p>
        </div>

        <div class="auth-single-foot">
            <img src="images/5.png" alt="">
            <img src="images/6.png" alt="">
            <img src="images/7.png" alt="">
            <img src="images/8.png" alt="">
            <img src="images/3.png" alt="">
        </div>

    </div>
</div>
</body>
</html>

