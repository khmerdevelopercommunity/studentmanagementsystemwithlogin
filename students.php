<?php
require_once 'db.php';

if (isset($_POST['save_student'])) {
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $dob        = $_POST['dob'];
    $gender     = $_POST['gender'];
    $medical    = $_POST['medical_notes'];
    $id         = $_POST['student_id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE students SET first_name=?, last_name=?, dob=?, gender=?, medical_notes=? WHERE student_id=?");
        $stmt->execute([$first_name, $last_name, $dob, $gender, $medical, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, dob, gender, medical_notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $dob, $gender, $medical]);
    }
    header("Location: students.php");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: students.php");
    exit;
}

$edit_student = isset($_GET['edit']) ? $pdo->prepare("SELECT * FROM students WHERE student_id = ?") : null;
if ($edit_student) { 
    $edit_student->execute([$_GET['edit']]); 
    $edit_student = $edit_student->fetch(); 
}

$students = $pdo->query("SELECT * FROM students ORDER BY student_id DESC")->fetchAll();

include 'header.php';
?>

<h2>Step 4: Students Management</h2>

<h3><?= $edit_student ? 'Edit Student' : 'Add New Student' ?></h3>

<form action="students.php" method="POST">
    <?php if ($edit_student): ?>
        <input type="hidden" name="student_id" value="<?= $edit_student['student_id'] ?>">
    <?php endif; ?>

    <p>
        <label>First Name:</label>
        <input type="text" name="first_name" value="<?= $edit_student['first_name'] ?? '' ?>" required>
    </p>
    <p>
        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?= $edit_student['last_name'] ?? '' ?>" required>
    </p>
    <p>
        <label>Date of Birth:</label>
        <input type="date" name="dob" value="<?= $edit_student['dob'] ?? '' ?>" required>
    </p>
    <p>
        <label>Gender:</label>
        <select name="gender" required>
            <option value="">-- Select Gender --</option>
            <option value="Male" <?= (isset($edit_student['gender']) && $edit_student['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= (isset($edit_student['gender']) && $edit_student['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= (isset($edit_student['gender']) && $edit_student['gender'] == 'Other') ? 'selected' : '' ?>>Other</option>
        </select>
    </p>
    <p>
        <label>Medical Notes / Special Information:</label>
        <textarea name="medical_notes"><?= $edit_student['medical_notes'] ?? '' ?></textarea>
    </p>
    <p>
        <button type="submit" name="save_student"><?= $edit_student ? 'Update Student' : 'Add Student' ?></button>
    </p>
</form>

<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px;">
    <h3>Students List</h3>
    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
        <a href="db_tools.php?action=export_data&table=all" class="btn-tool btn-tool-export">export all fields</a>
        <button type="button" class="btn-tool" style="background-color: #e53e3e; color: white; border-color: #c53030;" onclick="openReplaceImportModal('Replace All Database Data', 'all')">⚠️ replace import all</button>
    </div>
</div>

<table>
    <thead>
        <tr><th>ID</th><th>Name</th><th>DOB</th><th>Gender</th><th>Medical Notes</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach ($students as $s): ?>
            <tr>
                <td><?= $s['student_id'] ?></td>
                <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                <td><?= htmlspecialchars($s['dob']) ?></td>
                <td><?= htmlspecialchars($s['gender']) ?></td>
                <td><?= htmlspecialchars($s['medical_notes'] ?? 'None') ?></td>
                <td>
                    <a href="students.php?edit=<?= $s['student_id'] ?>">Edit</a> | 
                    <a href="students.php?delete=<?= $s['student_id'] ?>" class="btn-delete" onclick="return confirm('Delete this student?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>