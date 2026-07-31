<?php
require_once 'db.php';

// --- ENSURE THIS PAGE IS PROTECTED BY THE WALL ---
require_auth($conn); 

// --- FETCH REAL-TIME STATISTICS USING PDO ---
$total_teachers = 0;
$total_subjects = 0;
$total_classes  = 0;
$total_students = 0;
$total_enrolls  = 0;

try {
    $total_teachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    $total_subjects = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    $total_classes  = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
    $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $total_enrolls  = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
} catch (PDOException $e) {
    // Gracefully handle query error if tables are being set up
}

// YOUR ORIGINAL INCLUDES
include 'header.php';
?>

<!-- Success/Error Messages -->
<?php if (isset($_SESSION['delete_success'])): ?>
    <div style="background: #c6f6d5; color: #22543d; padding: 15px; margin-bottom: 15px; border-radius: 4px; border-left: 4px solid #22543d;">
        <?= htmlspecialchars($_SESSION['delete_success']) ?>
        <?php unset($_SESSION['delete_success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['delete_error'])): ?>
    <div style="background: #fed7d7; color: #742a2a; padding: 15px; margin-bottom: 15px; border-radius: 4px; border-left: 4px solid #742a2a;">
        <?= htmlspecialchars($_SESSION['delete_error']) ?>
        <?php unset($_SESSION['delete_error']); ?>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
    <div style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0; text-align:center; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
        <h3 style="margin:0; color:#38bdf8; font-size:32px;"><?= $total_teachers ?></h3>
        <p style="margin:5px 0 0 0; color:#64748b; font-size: 14px; font-weight: 600; text-transform: uppercase;">Teachers</p>
    </div>
    <div style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0; text-align:center; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
        <h3 style="margin:0; color:#38bdf8; font-size:32px;"><?= $total_subjects ?></h3>
        <p style="margin:5px 0 0 0; color:#64748b; font-size: 14px; font-weight: 600; text-transform: uppercase;">Subjects</p>
    </div>
    <div style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0; text-align:center; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
        <h3 style="margin:0; color:#38bdf8; font-size:32px;"><?= $total_classes ?></h3>
        <p style="margin:5px 0 0 0; color:#64748b; font-size: 14px; font-weight: 600; text-transform: uppercase;">Classes</p>
    </div>
    <div style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0; text-align:center; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
        <h3 style="margin:0; color:#38bdf8; font-size:32px;"><?= $total_students ?></h3>
        <p style="margin:5px 0 0 0; color:#64748b; font-size: 14px; font-weight: 600; text-transform: uppercase;">Students</p>
    </div>
    <div style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0; text-align:center; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
        <h3 style="margin:0; color:#38bdf8; font-size:32px;"><?= $total_enrolls ?></h3>
        <p style="margin:5px 0 0 0; color:#64748b; font-size: 14px; font-weight: 600; text-transform: uppercase;">Enrollments</p>
    </div>
</div>

<!-- Quick Actions Section -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 30px; padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
        <a href="db_tools.php?action=export_data&table=all" class="btn-tool btn-tool-export" style="padding: 10px 16px;">
            📥 export all fields
        </a>
        <button type="button" class="btn-tool" style="background-color: #e53e3e; color: white; border-color: #c53030; padding: 10px 16px;" onclick="openReplaceImportModal('Replace All Database Data', 'all')">
            ⚠️ replace import all
        </button>
    </div>
</div>

<h2>Welcome! Follow the steps in order:</h2>
<ol style="margin-left: 20px; line-height: 2;">
    <li><strong>Step 1: Teachers</strong> — Add teacher records first.</li>
    <li><strong>Step 2: Subjects</strong> — Add core primary subjects (Math, Reading, etc.).</li>
    <li><strong>Step 3: Classes</strong> — Create Grade 1-6 sections and assign Homeroom Teachers.</li>
    <li><strong>Step 4: Students</strong> — Add student information and medical notes.</li>
    <li><strong>Step 5: Enrollments</strong> — Assign registered students into created classes.</li>
</ol>

<!-- Delete All Data Section -->
<div id="delete-section" style="margin-top: 40px; padding: 20px; border: 2px solid #e53e3e; border-radius: 8px; background: #fff5f5;">
    <h3 style="color: #e53e3e; display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 24px;">⚠️</span> Danger Zone
    </h3>
    <p style="color: #718096; font-size: 14px;">
        <strong>Warning:</strong> This will permanently delete ALL school data (teachers, students, classes, enrollments, grades, etc.).
        <br>Your login credentials and audit logs will be preserved.
    </p>
    
    <button type="button" onclick="openDeleteModal()" style="background-color: #e53e3e; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 14px;">
        🗑️ Delete All School Data
    </button>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 450px; border-left: 4px solid #e53e3e;">
        <h3 style="color: #e53e3e; margin-top: 0;">⚠️ Confirm Data Deletion</h3>
        <p style="color: #718096; font-size: 14px;">
            <strong>This action cannot be undone!</strong><br>
            All school data will be permanently deleted.
            <br><br>
            <strong>What will be deleted:</strong>
            <ul style="color: #4a5568; font-size: 13px; margin: 5px 0;">
                <li>✅ All teachers</li>
                <li>✅ All students</li>
                <li>✅ All classes</li>
                <li>✅ All enrollments</li>
                <li>✅ All grades</li>
                <li>✅ All attendance records</li>
                <li>✅ All guardians</li>
            </ul>
            <br>
            <strong style="color: #38a169;">What will be preserved:</strong>
            <ul style="color: #4a5568; font-size: 13px; margin: 5px 0;">
                <li>✅ Your login account</li>
                <li>✅ Audit logs (security history)</li>
            </ul>
        </p>
        
        <form action="db_tools.php?action=delete_all_data" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div style="margin: 15px 0;">
                <label style="font-weight: 600; font-size: 13px; color: #4a5568; display: block; margin-bottom: 5px;">
                    Enter your password to confirm:
                </label>
                <input type="password" name="password" placeholder="Enter your password" required 
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px;">
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeDeleteModal()" class="btn-tool" style="padding: 10px 20px;">
                    Cancel
                </button>
                <button type="submit" style="background-color: #e53e3e; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer;">
                    🗑️ Yes, Delete All Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal() {
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    }
});
</script>

<style>
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.modal-box {
    background: #ffffff;
    padding: 25px;
    border-radius: 8px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
</style>

</div> </body>
</html>