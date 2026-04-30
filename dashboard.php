<?php
session_start();
require_once 'db.php';

// login check - use name and role for top of page info
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }
$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — AP Cyber Field Guide</title>
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

    <!-- big header area (hero), name converted to safe chars -->
    <div class="dashboard-hero">
        <div class="dashboard-hero-left">
            <div class="hero-eyebrow">Welcome back</div>
            <span class="hero-name"><?php echo htmlspecialchars($username); ?>'s</span>
            <span class="hero-guide">Field Guide</span>
            <div class="hero-classify">
                <div class="classify-pair">
                    <span class="classify-label">Role</span>
                    <span class="classify-val"><?php echo ucfirst($role); ?></span>
                </div>
                <div class="classify-pair">
                    <span class="classify-label">Course</span>
                    <span class="classify-val">AP Cybersecurity</span>
                </div>
            </div>
        </div>
        <!-- decorative image grid -->
        <div class="dashboard-hero-right">
            <img src="images/1.png" alt="">
            <img src="images/2.png" alt="">
            <img src="images/6.png" alt="">
            <img src="images/5.png" alt="">
        </div>
    </div>

    <!-- visible card sets - based on role -->
    <?php if ($role === 'student'): ?>
    <div class="dashboard-cards">
        <div class="card">
            <div class="card-entry-header">
                <span class="card-entry-num">Entry 01</span>
                <span class="card-entry-tag">Browse</span>
            </div>
            <div class="card-entry-body">
                <div class="card-entry-icon"><img src="images/7.png" alt=""></div>
                <div class="card-entry-info">
                    <h2>Field Guide</h2>
                    <p class="card-entry-desc">Browse all cybersecurity concepts organized by unit. Filter by topic and get AI practice prompts.</p>
                    <div class="card-entry-meta">
                        <span class="meta-tag">5 Units</span>
                        <span class="meta-tag">50+ Terms</span>
                        <span class="meta-tag">AI Prompts</span>
                    </div>
                </div>
            </div>
            <div class="card-entry-footer">
                <a href="field-guide.php" class="btn-entry">Browse Concepts</a>
            </div>
        </div>

        <!-- cards styled as field guide entries -->
        <div class="card">
            <div class="card-entry-header">
                <span class="card-entry-num">Entry 02</span>
                <span class="card-entry-tag">Review</span>
            </div>
            <div class="card-entry-body">
                <div class="card-entry-icon"><img src="images/5.png" alt=""></div>
                <div class="card-entry-info">
                    <h2>Bookmarks</h2>
                    <p class="card-entry-desc">Review concepts you've flagged for later. Build your personal study list before an exam.</p>
                    <div class="card-entry-meta">
                        <span class="meta-tag">Saved</span>
                        <span class="meta-tag">Exam Prep</span>
                    </div>
                </div>
            </div>
            <div class="card-entry-footer">
                <a href="bookmarks.php" class="btn-entry">View Bookmarks</a>
            </div>
        </div>

        <div class="card">
            <div class="card-entry-header">
                <span class="card-entry-num">Entry 03</span>
                <span class="card-entry-tag">Track</span>
            </div>
            <div class="card-entry-body">
                <div class="card-entry-icon"><img src="images/8.png" alt=""></div>
                <div class="card-entry-info">
                    <h2>My Progress</h2>
                    <p class="card-entry-desc">Track which concepts you've mastered across all five College Board units.</p>
                    <div class="card-entry-meta">
                        <span class="meta-tag">Not Started</span>
                        <span class="meta-tag">In Progress</span>
                        <span class="meta-tag">Mastered</span>
                    </div>
                </div>
            </div>
            <div class="card-entry-footer">
                <a href="progress.php" class="btn-entry">View Progress</a>
            </div>
        </div>
    </div>

    <?php elseif ($role === 'teacher'): ?>
    <div class="dashboard-cards">
        <div class="card">
            <div class="card-entry-header">
                <span class="card-entry-num">Entry 01</span>
                <span class="card-entry-tag">Browse</span>
            </div>
            <div class="card-entry-body">
                <div class="card-entry-icon"><img src="images/7.png" alt=""></div>
                <div class="card-entry-info">
                    <h2>Field Guide</h2>
                    <p class="card-entry-desc">Browse all cybersecurity concepts organized by unit across the full AP curriculum.</p>
                    <div class="card-entry-meta">
                        <span class="meta-tag">5 Units</span>
                        <span class="meta-tag">50+ Terms</span>
                    </div>
                </div>
            </div>
            <div class="card-entry-footer">
                <a href="field-guide.php" class="btn-entry">Browse Concepts</a>
            </div>
        </div>

        <div class="card">
            <div class="card-entry-header">
                <span class="card-entry-num">Entry 02</span>
                <span class="card-entry-tag">Manage</span>
            </div>
            <div class="card-entry-body">
                <div class="card-entry-icon"><img src="images/9.png" alt=""></div>
                <div class="card-entry-info">
                    <h2>Manage Concepts</h2>
                    <p class="card-entry-desc">Add, edit, or delete concept entries. Keep the field guide current with your classroom.</p>
                    <div class="card-entry-meta">
                        <span class="meta-tag">Add</span>
                        <span class="meta-tag">Edit</span>
                        <span class="meta-tag">Delete</span>
                    </div>
                </div>
            </div>
            <div class="card-entry-footer">
                <a href="manage-concepts.php" class="btn-entry">Open Panel</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="footbar">
        <span>AP Cyber Field Guide</span>
        <span><?php echo ucfirst($role); ?> View</span>
        <span></span>
    </div>

</div>
</div>
</body>
</html>