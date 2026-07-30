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

<h2>Welcome! Follow the steps in order:</h2>
<ol style="margin-left: 20px; line-height: 2;">
    <li><strong>Step 1: Teachers</strong> — Add teacher records first.</li>
    <li><strong>Step 2: Subjects</strong> — Add core primary subjects (Math, Reading, etc.).</li>
    <li><strong>Step 3: Classes</strong> — Create Grade 1-6 sections and assign Homeroom Teachers.</li>
    <li><strong>Step 4: Students</strong> — Add student information and medical notes.</li>
    <li><strong>Step 5: Enrollments</strong> — Assign registered students into created classes.</li>
</ol>

</div> </body>
</html>