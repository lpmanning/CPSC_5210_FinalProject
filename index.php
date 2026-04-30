<!-- if already logged in - go to dashboard -->
<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AP Cyber Field Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-single">

<!-- decorative images -->

    <div class="auth-single-shapes">
        <img src="images/1.png" alt="">
        <img src="images/2.png" alt="">
        <img src="images/9.png" alt="">
        <img src="images/3.png" alt="">
    </div>

    <div class="auth-single-body">

        <div class="auth-single-headline">
            <span class="auth-headline-serif">AP Cyber</span>
            <span class="auth-headline-condensed">Field Guide</span>
        </div>

        <!-- login form -->

        <div class="auth-single-form">
            <p class="auth-form-eyebrow">Student and Teacher Access</p>

            <!-- check for error with login -->
            <?php if (isset($_GET['error'])): ?>
                <div class="error-msg">
                    <?php
                        if ($_GET['error'] === 'invalid') echo 'Invalid username or password.';
                        if ($_GET['error'] === 'required') echo 'Please fill in all fields.';
                    ?>
                </div>
            <?php endif; ?>

            <!-- login form -->
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-primary">Sign In</button>
            </form>
            <p class="switch-link">No account? <a href="register.php">Register here</a></p>
        </div>

        <!-- decorative images -->
        <div class="auth-single-foot">
            <img src="images/4.png" alt="">
            <img src="images/5.png" alt="">
            <img src="images/6.png" alt="">
            <img src="images/7.png" alt="">
            <img src="images/8.png" alt="">
        </div>

    </div>
</div>
</body>
</html>

