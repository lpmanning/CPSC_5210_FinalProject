<!-- same login security as other pages -->
<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress — AP Cyber Field Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">
<div class="page-content">

<!-- nav bar -->
    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">
            <div class="nav-brand-text"><em>AP Cyber</em> <span>Field Guide</span></div>
        </a>
        <div class="nav-links">
            <a href="field-guide.php">Field Guide</a>
            <?php if ($role === 'student'): ?>
                <a href="bookmarks.php">Bookmarks</a>
                <a href="progress.php">Progress</a>
            <?php endif; ?>
            <?php if ($role === 'teacher'): ?>
                <a href="manage-concepts.php">Manage</a>
            <?php endif; ?>
            <a href="logout.php" class="nav-logout">Log Out</a>
        </div>
    </nav>

    <!-- page header with stats -->
    <div class="guide-header">
        <div class="guide-header-left">
            <div class="hero-eyebrow">Mastery Tracker</div>
            <span class="hero-name">My</span>
            <span class="hero-guide">Progress</span>
            <p class="guide-desc">Track which concepts you've marked as in progress or mastered. Update your status from the field guide entry panel.</p>
        </div>
        <div class="guide-header-right">
            <div class="guide-stats">
                <div class="stat-block">
                    <span class="stat-num" id="stat-mastered">—</span>
                    <span class="stat-label">Mastered</span>
                </div>
                <div class="stat-block">
                    <span class="stat-num" id="stat-progress">—</span>
                    <span class="stat-label">In Progress</span>
                </div>
            </div>
        </div>
    </div>

    <div id="loading-state" class="guide-loading"><span>Loading progress...</span></div>
    <div id="progress-list" style="display:none;"></div>
    <div id="empty-state" class="guide-empty" style="display:none;">
        <p>No progress tracked yet.</p>
        <span>Open any concept in the field guide and mark your status.</span>
    </div>

    <div class="footbar">
        <span>AP Cyber Field Guide</span>
        <span>Mastery Tracker</span>
        <span></span>
    </div>

</div>
</div>
<script src="progress.js"></script>
</body>
</html>

