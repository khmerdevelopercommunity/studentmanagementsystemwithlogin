<?php
require_once 'db.php';
// --- ENSURE THIS PAGE IS PROTECTED BY THE WALL ---
require_auth($conn); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Primary School Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .operational-bar { background: #1e293b; color: #f8fafc; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; font-size: 14px; flex-wrap: wrap; gap: 10px;}
        .operational-bar .sign-out-btn { background: #ef4444; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 13px; transition: background 0.2s; white-space: nowrap;}
        .operational-bar .sign-out-btn:hover { background: #dc2626; text-decoration: none; }
        
        /* Mobile hamburger menu */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #ecf0f1;
            font-size: 24px;
            cursor: pointer;
            padding: 5px 10px;
        }
        
        /* Search container - ensure proper positioning */
        .search-container {
            position: relative;
            display: inline-block;
        }
        
        #live-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: auto;
            min-width: 280px;
            max-width: 400px;
            width: auto;
            background: #ffffff;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: none;
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* Align to right when needed */
        .search-container.align-right #live-search-results {
            left: auto;
            right: 0;
        }
        
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            .nav-links {
                display: none;
                flex-direction: column;
                width: 100%;
                gap: 4px;
            }
            .nav-links.open {
                display: flex;
            }
            .nav-links a {
                padding: 8px 12px;
                width: 100%;
                text-align: center;
            }
            .header-tools {
                flex: 1;
                justify-content: flex-end;
            }
            #live-search-results {
                left: 0;
                right: 0;
                min-width: auto;
                width: 100%;
                max-width: 100%;
                border-radius: 0 0 6px 6px;
            }
            .search-container.align-right #live-search-results {
                left: 0;
                right: 0;
            }
        }
        
        @media (max-width: 480px) {
            #live-search-results {
                max-height: 300px;
            }
        }
    </style>
</head>
<body>

<div class="operational-bar">
    <div>Secured Operational Space Entity: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></div>
    <a href="logout.php" class="sign-out-btn">Terminate Session (Sign Out)</a>
</div>

<h1>Primary School Management System (Grades 1-6)</h1>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'import_success'): ?>
    <div class="alert alert-success">
        Database imported successfully!
    </div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'import_error'): ?>
    <div class="alert alert-error">
        Import failed! Please check your .sql file.
    </div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'replace_import_success'): ?>
    <div class="alert alert-success">
        Database replaced and imported successfully!
    </div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'replace_import_error'): ?>
    <div class="alert alert-error">
        Replace import failed! Please check your .sql file.
        <?php if (isset($_GET['error'])): ?>
            <br><small>Error: <?= htmlspecialchars($_GET['error']) ?></small>
        <?php endif; ?>
    </div>
<?php endif; ?>

<nav>
    <button class="menu-toggle" onclick="toggleMenu()">☰ Menu</button>
    <div class="nav-links" id="navLinks">
        <a href="index.php">Dashboard</a>
        <a href="teachers.php">1. Teachers</a>
        <a href="subjects.php">2. Subjects</a>
        <a href="classes.php">3. Classes</a>
        <a href="students.php">4. Students</a>
        <a href="enroll.php">5. Enrollments</a>
    </div>

    <div class="header-tools">
        <div class="search-container" id="searchContainer">
            <form action="search.php" method="GET" class="search-form" style="margin:0; padding:0; background:none; border:none; box-shadow:none;">
                <input type="text" id="live-search-input" name="q" placeholder="Search..." autocomplete="off" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
                <button type="submit">Search</button>
            </form>
            <div id="live-search-results"></div>
        </div>
    </div>
</nav>

<!-- Import Modal -->
<div id="importModal" class="modal-overlay">
    <div class="modal-box">
        <h3 id="importModalTitle">Import SQL File</h3>
        <p style="font-size: 12px; color: #718096;">Select a <code>.sql</code> file to restore data:</p>
        <form action="db_tools.php?action=import" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="table_target" id="importTableTarget" value="all">
            <input type="file" name="sql_file" accept=".sql" required style="margin-bottom: 15px; width: 100%; padding: 8px;">
            <div style="text-align: right; display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap;">
                <button type="button" onclick="closeImportModal()" class="btn-tool">Cancel</button>
                <button type="submit" class="btn-tool btn-tool-import">Upload & Import</button>
            </div>
        </form>
    </div>
</div>

<!-- Replace Import Modal -->
<div id="replaceImportModal" class="modal-overlay">
    <div class="modal-box" style="border-left: 4px solid #e53e3e;">
        <h3 id="replaceImportModalTitle" style="color: #e53e3e;">⚠️ Replace Import</h3>
        <p style="font-size: 12px; color: #718096;">
            <strong style="color: #e53e3e;">Warning:</strong> This will DELETE ALL existing data in the selected table(s) 
            and replace it with the data from the <code>.sql</code> file.
        </p>
        <p style="font-size: 12px; color: #718096; margin-top: 8px;">
            Select a <code>.sql</code> file to replace data:
        </p>
        <form action="db_tools.php?action=replace_import" method="POST" enctype="multipart/form-data" 
              onsubmit="return confirm('⚠️ WARNING: This will replace all existing data. Are you sure you want to continue?');">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="table_target" id="replaceImportTableTarget" value="all">
            <input type="file" name="sql_file" accept=".sql" required style="margin-bottom: 15px; width: 100%; padding: 8px;">
            <div style="text-align: right; display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap;">
                <button type="button" onclick="closeReplaceImportModal()" class="btn-tool">Cancel</button>
                <button type="submit" class="btn-tool btn-tool-replace">
                    Replace All Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleMenu() {
    document.getElementById('navLinks').classList.toggle('open');
}

function openImportModal(titleText, targetTable) {
    document.getElementById('importModalTitle').innerText = titleText;
    document.getElementById('importTableTarget').value = targetTable;
    document.getElementById('importModal').style.display = 'flex';
}

function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
}

function openReplaceImportModal(titleText, targetTable) {
    document.getElementById('replaceImportModalTitle').innerText = '⚠️ ' + titleText;
    document.getElementById('replaceImportTableTarget').value = targetTable;
    document.getElementById('replaceImportModal').style.display = 'flex';
}

function closeReplaceImportModal() {
    document.getElementById('replaceImportModal').style.display = 'none';
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal-overlay');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });

    const searchInput = document.getElementById('live-search-input');
    const resultsBox = document.getElementById('live-search-results');
    const searchContainer = document.getElementById('searchContainer');

    if (!searchInput || !resultsBox) return;

    function highlightText(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }

    // FIX: Check search position to prevent cutting off
    function checkSearchPosition() {
        if (!searchContainer) return;
        const rect = searchContainer.getBoundingClientRect();
        const windowWidth = window.innerWidth;
        
        // On mobile, always left-aligned (full width)
        if (windowWidth <= 768) {
            searchContainer.classList.remove('align-right');
        } 
        // If search is near the right edge, align dropdown to right
        else if (rect.right > windowWidth - 100) {
            searchContainer.classList.add('align-right');
        } 
        // Otherwise align to left
        else {
            searchContainer.classList.remove('align-right');
        }
    }

    // Check on load
    checkSearchPosition();

    // Check on resize
    window.addEventListener('resize', checkSearchPosition);

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
                    resultsBox.innerHTML = '<div class="search-no-results">🔍 No matches found</div>';
                    resultsBox.style.display = 'block';
                }
                
                // Re-check position after results appear
                checkSearchPosition();
            });
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.style.display = 'none';
        }
    });
});

// Close mobile menu on window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        document.getElementById('navLinks').classList.remove('open');
    }
});
</script>
<div class="content-wrapper" style="padding: 20px;">