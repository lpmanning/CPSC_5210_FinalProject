<!-- more strict securoty - only teachers can manage concepts! -->

<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Concepts — AP Cyber Field Guide</title>
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
            <a href="manage-concepts.php">Manage</a>
            <a href="logout.php" class="nav-logout">Log Out</a>
        </div>
    </nav>

    <!-- header with add entry button added -->
    <div class="guide-header">
        <div class="guide-header-left">
            <div class="hero-eyebrow">Teacher Panel</div>
            <span class="hero-name">Manage</span>
            <span class="hero-guide">Concepts</span>
            <p class="guide-desc">Add new concept entries, edit existing ones, or remove outdated entries. Changes appear in the field guide immediately.</p>
        </div>
        <div class="guide-header-right">
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
            <button class="btn-primary" id="add-btn" style="width:auto;margin-top:16px;">Add New Entry</button>
        </div>
    </div>

    <div class="unit-filter" id="unit-filter">
        <button class="filter-btn active" data-unit="">All Units</button>
    </div>

    <div id="loading-state" class="guide-loading"><span>Loading entries...</span></div>
    <div id="manage-list" style="display:none;"></div>
    <div id="empty-state" class="guide-empty" style="display:none;">
        <p>No entries found.</p>
        <span>Add a new concept using the button above.</span>
    </div>

    <!-- add/edit modal -->
    <div id="modal-overlay" class="detail-overlay" style="display:none;">
        <div class="detail-panel">
            <div class="detail-panel-header">
                <span class="detail-unit-label" id="modal-title">Add New Entry</span>
                <button class="detail-close" id="modal-close">Close</button>
            </div>
            <div class="detail-panel-body">
                <div id="modal-msg"></div>
                <div class="form-group">
                    <label>Unit</label>
                    <select id="f-unit"></select>
                </div>
                <div class="form-group">
                    <label>Term</label>
                    <input type="text" id="f-term" placeholder="e.g. Phishing">
                </div>
                <div class="form-group">
                    <label>Definition</label>
                    <textarea id="f-definition" rows="4" placeholder="Clear, concise definition..."></textarea>
                </div>
                <div class="form-group">
                    <label>Example (optional)</label>
                    <textarea id="f-example" rows="3" placeholder="Real-world example scenario..."></textarea>
                </div>
                <div class="form-group">
                    <label>AI Practice Prompt (optional)</label>
                    <textarea id="f-prompt" rows="3" placeholder="Prompt students paste into an AI tool..."></textarea>
                </div>
                <input type="hidden" id="f-id">
                <div style="display:flex;gap:10px;margin-top:8px;">
                    <button class="btn-primary" id="modal-save" style="flex:1;">Save Entry</button>
                    <button class="btn-secondary" id="modal-cancel">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- confirm before deleting -->
    <div id="delete-overlay" class="detail-overlay" style="display:none;">
        <div class="detail-panel" style="max-width:380px;">
            <div class="detail-panel-header">
                <span class="detail-unit-label">Confirm Delete</span>
                <button class="detail-close" id="delete-close">Close</button>
            </div>
            <div class="detail-panel-body">
                <p style="font-family:'Libre Baskerville',serif;font-style:italic;font-size:1.1rem;color:var(--purple);margin-bottom:12px;" id="delete-term-name"></p>
                <p style="font-family:'Courier Prime',monospace;font-size:11px;letter-spacing:.08em;color:var(--text-muted);margin-bottom:24px;">This will permanently remove the entry from the field guide. This cannot be undone.</p>
                <div style="display:flex;gap:10px;">
                    <button class="btn-danger" id="confirm-delete-btn" style="flex:1;">Delete Entry</button>
                    <button class="btn-secondary" id="cancel-delete-btn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="footbar">
        <span>AP Cyber Field Guide</span>
        <span>Teacher Panel</span>
        <span></span>
    </div>

</div>
</div>
<script src="manage.js"></script>
</body>
</html>

