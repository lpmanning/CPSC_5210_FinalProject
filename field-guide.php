<!-- same login/security as other pages -->
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
    <title>Field Guide — AP Cyber</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">
<div class="page-content">

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

    <!-- field guide header -->

    <div class="guide-header">
        <div class="guide-header-left">
            <div class="hero-eyebrow">Browsable Reference</div>
            <span class="hero-name">Field</span>
            <span class="hero-guide">Guide</span>
            <p class="guide-desc">All AP Cybersecurity concepts organized by College Board unit. Click any entry to view the full definition, example, and AI practice prompt.</p>
        </div>
        <div class="guide-header-right">
            <div class="guide-search-wrap">
                <label>Search entries</label>
                <input type="text" id="search-input" placeholder="e.g. phishing, firewall...">
            </div>
            <div class="guide-stats">
                <div class="stat-block">
                    <span class="stat-num" id="stat-count">—</span>
                    <span class="stat-label">Entries</span>
                </div>
                <div class="stat-block">
                    <span class="stat-num">5</span>
                    <span class="stat-label">Units</span>
                </div>
            </div>
        </div>
    </div>

<!-- unit filter -->
    <div class="unit-filter" id="unit-filter">
        <button class="filter-btn active" data-unit="">All Units</button>
    </div>

    <!-- 3 states - similar to bookmarks -->
    <div id="loading-state" class="guide-loading"><span>Loading entries...</span></div>
    <div id="concepts-grid" class="concepts-grid" style="display:none;"></div>
    <div id="empty-state" class="guide-empty" style="display:none;">
        <p>No entries found.</p>
        <span>Try a different unit or search term.</span>
    </div>

    <!-- fill in info upon term selection -->
    <div id="detail-overlay" class="detail-overlay" style="display:none;">
        <div class="detail-panel">
            <div class="detail-panel-header">
                <div>
                    <span class="detail-unit-label" id="detail-unit"></span>
                    <span class="detail-entry-num" id="detail-num"></span>
                </div>
                <button class="detail-close" id="detail-close">Close</button>
            </div>
            <div class="detail-panel-body">
                <h2 id="detail-term"></h2>
                <p class="detail-unit-name" id="detail-unit-name"></p>

                <div class="detail-section">
                    <div class="detail-section-label">Definition</div>
                    <p id="detail-definition"></p>
                </div>

                <div class="detail-section" id="detail-example-wrap">
                    <div class="detail-section-label">Example</div>
                    <p id="detail-example"></p>
                </div>

                <?php if ($role === 'student'): ?>
                <div class="detail-section">
                    <div class="detail-section-label">AI Practice Prompt</div>
                    <div id="prompt-area">
                        <button class="btn-primary" id="get-prompt-btn" style="width:auto;">Get Practice Prompt</button>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-section-label">Actions</div>
                    <div class="detail-actions">
                        <button class="btn-secondary" id="bookmark-btn">Bookmark Entry</button>
                        <div class="progress-select-wrap">
                            <label>Mark progress:</label>
                            <select id="progress-select">
                                <option value="not_started">Not Started</option>
                                <option value="in_progress">In Progress</option>
                                <option value="mastered">Mastered</option>
                            </select>
                            <button class="btn-secondary" id="save-progress-btn">Save</button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="footbar">
        <span>AP Cyber Field Guide</span>
        <span id="footbar-unit">All Units</span>
        <span></span>
    </div>

</div>
</div>
<script>const ROLE = '<?php echo $role; ?>';</script>
<script src="guide.js"></script>
</body>
</html>