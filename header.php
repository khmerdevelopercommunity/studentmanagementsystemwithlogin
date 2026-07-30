<?php
require_once 'db.php';
// --- ENSURE THIS PAGE IS PROTECTED BY THE WALL ---
require_auth($conn); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Primary School Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .operational-bar { background: #1e293b; color: #f8fafc; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; font-size: 14px;}
        .operational-bar .sign-out-btn { background: #ef4444; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 13px; transition: background 0.2s;}
        .operational-bar .sign-out-btn:hover { background: #dc2626; }
    </style>
</head>
<body>

<div class="operational-bar">
    <div>Secured Operational Space Entity: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></div>
    <a href="logout.php" class="sign-out-btn">Terminate Session (Sign Out)</a>
</div>

<h1>Primary School Management System (Grades 1-6)</h1>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'import_success'): ?>
    <div style="background: #c6f6d5; color: #22543d; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
        Database imported successfully!
    </div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'import_error'): ?>
    <div style="background: #fed7d7; color: #742a2a; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
        Import failed! Please check your .sql file.
    </div>
<?php endif; ?>

<nav>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="teachers.php">1. Teachers</a>
        <a href="subjects.php">2. Subjects</a>
        <a href="classes.php">3. Classes</a>
        <a href="students.php">4. Students</a>
        <a href="enroll.php">5. Enrollments</a>
    </div>

    <div class="header-tools">
        <div class="search-container">
            <form action="search.php" method="GET" class="search-form" style="margin:0; padding:0; background:none; border:none; box-shadow:none;">
                <input type="text" id="live-search-input" name="q" placeholder="Search..." autocomplete="off" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
                <button type="submit">Search</button>
            </form>
            <div id="live-search-results"></div>
        </div>
    </div>
</nav>

<div id="importModal" class="modal-overlay">
    <div class="modal-box">
        <h3 id="importModalTitle">Import SQL File</h3>
        <p style="font-size: 12px; color: #718096;">Select a <code>.sql</code> file to restore data:</p>
        <form action="db_tools.php?action=import" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="table_target" id="importTableTarget" value="all">
            <input type="file" name="sql_file" accept=".sql" required style="margin-bottom: 15px; width: 100%;">
            <div style="text-align: right;">
                <button type="button" onclick="closeImportModal()" class="btn-tool">Cancel</button>
                <button type="submit" class="btn-tool btn-tool-import">Upload & Import</button>
            </div>
        </form>
    </div>
</div>

<script>
function openImportModal(titleText, targetTable) {
    document.getElementById('importModalTitle').innerText = titleText;
    document.getElementById('importTableTarget').value = targetTable;
    document.getElementById('importModal').style.display = 'flex';
}

function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('live-search-input');
    const resultsBox = document.getElementById('live-search-results');

    if (!searchInput || !resultsBox) return;

    function highlightText(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        if (query.length === 0) {
            resultsBox.style.display = 'none';
            resultsBox.innerHTML = '';
            return;
        }

        fetch('ajax_search.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                let html = '';
                let totalFound = 0;

                if (data.teachers && data.teachers.length > 0) {
                    html += '<div class="search-group-title">Teachers</div>';
                    data.teachers.forEach(item => {
                        totalFound++;
                        html += `<a class="search-item" href="teachers.php?edit=${item.teacher_id}"><div>${highlightText(item.title, query)}</div></a>`;
                    });
                }
                if (data.subjects && data.subjects.length > 0) {
                    html += '<div class="search-group-title">Subjects</div>';
                    data.subjects.forEach(item => {
                        totalFound++;
                        html += `<a class="search-item" href="subjects.php?edit=${item.subject_id}"><div>${highlightText(item.title, query)}</div></a>`;
                    });
                }
                if (data.classes && data.classes.length > 0) {
                    html += '<div class="search-group-title">Classes</div>';
                    data.classes.forEach(item => {
                        totalFound++;
                        html += `<a class="search-item" href="classes.php?edit=${item.class_id}"><div>${highlightText(item.title, query)}</div></a>`;
                    });
                }
                if (data.students && data.students.length > 0) {
                    html += '<div class="search-group-title">Students</div>';
                    data.students.forEach(item => {
                        totalFound++;
                        html += `<a class="search-item" href="students.php?edit=${item.student_id}"><div>${highlightText(item.title, query)}</div></a>`;
                    });
                }

                if (totalFound > 0) {
                    resultsBox.innerHTML = html;
                    resultsBox.style.display = 'block';
                } else {
                    resultsBox.innerHTML = '<div class="search-item" style="color:#a0aec0;">No matches found</div>';
                    resultsBox.style.display = 'block';
                }
            });
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.style.display = 'none';
        }
    });
});
</script>
<div class="content-wrapper" style="padding: 20px;">