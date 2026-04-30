
<?php
session_start();

// database connection file
require_once 'db.php';

// login check, select role
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">

<!-- header -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookmarks — AP Cyber Field Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">
<div class="page-content">

<!-- conditional nav bar -->
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

    <!-- page header -->
    <div class="guide-header">
        <div class="guide-header-left">
            <div class="hero-eyebrow">Saved for Review</div>
            <span class="hero-name">Book</span>
            <span class="hero-guide">Marks</span>
            <p class="guide-desc">Concepts you've flagged for later study. Use this list to build your exam prep sessions and remove entries as you master them.</p>
        </div>
        <div class="guide-header-right">
            <div class="guide-stats">
                <div class="stat-block">
                    <span class="stat-num" id="stat-count">—</span>
                    <span class="stat-label">Saved</span>
                </div>
                <div class="stat-block">
                    <span class="stat-num" id="stat-units">—</span>
                    <span class="stat-label">Units</span>
                </div>
            </div>
        </div>
    </div>

    <div id="loading-state" class="guide-loading"><span>Loading bookmarks...</span></div>
    <div id="bookmarks-list" style="display:none;"></div>
    <div id="empty-state" class="guide-empty" style="display:none;">
        <p>No bookmarks yet.</p>
        <span>Browse the field guide and bookmark entries to see them here.</span>
    </div>

    <div class="footbar">
        <span>AP Cyber Field Guide</span>
        <span>Saved Entries</span>
        <span></span>
    </div>

</div>
</div>
<script src="bookmarks.js"></script>
</body>
</html>