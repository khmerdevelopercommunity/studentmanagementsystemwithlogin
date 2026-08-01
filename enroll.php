<?php
require_once 'db.php';

// Save or Update Enrollment
if (isset($_POST['save_enrollment'])) {
    $student_id = $_POST['student_id'];
    $class_id   = $_POST['class_id'];
    $id         = $_POST['enrollment_id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE enrollments SET student_id=?, class_id=? WHERE enrollment_id=?");
        $stmt->execute([$student_id, $class_id, $id]);
    } else {
        $check = $pdo->prepare("SELECT * FROM enrollments WHERE student_id = ? AND class_id = ?");
        $check->execute([$student_id, $class_id]);

        if ($check->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, class_id) VALUES (?, ?)");
            $stmt->execute([$student_id, $class_id]);
        }
    }

    header("Location: enroll.php");
    exit;
}

// Delete Enrollment
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE enrollment_id = ?");
    $stmt->execute([$_GET['delete']]);

    header("Location: enroll.php");
    exit;
}

// Fetch edit data
$edit_enrollment = isset($_GET['edit']) ? $pdo->prepare("SELECT * FROM enrollments WHERE enrollment_id = ?") : null;
if ($edit_enrollment) {
    $edit_enrollment->execute([$_GET['edit']]);
    $edit_enrollment = $edit_enrollment->fetch();
}

$students = $pdo->query("SELECT * FROM students ORDER BY first_name ASC")->fetchAll();
$classes  = $pdo->query("SELECT * FROM classes ORDER BY grade_level ASC, section ASC")->fetchAll();
$enrollments = $pdo->query("
    SELECT e.enrollment_id, e.student_id, e.class_id, CONCAT(s.first_name, ' ', s.last_name) AS student_name, c.grade_level, c.section, c.academic_year
    FROM enrollments e
    JOIN students s ON e.student_id = s.student_id
    JOIN classes c ON e.class_id = c.class_id
    ORDER BY e.enrollment_id DESC
")->fetchAll();

include 'header.php';
?>

<h2>Step 5: Student Enrollments</h2>

<h3><?= $edit_enrollment ? 'Edit Enrollment' : 'Enroll Student in a Class' ?></h3>

<form action="enroll.php" method="POST">
    <?php if ($edit_enrollment): ?>
        <input type="hidden" name="enrollment_id" value="<?= $edit_enrollment['enrollment_id'] ?>">
    <?php endif; ?>

    <p>
        <label>Select Student:</label>
        <select name="student_id" required>
            <option value="">-- Select Student --</option>
            <?php foreach ($students as $s): ?>
                <option value="<?= $s['student_id'] ?>" <?= (isset($edit_enrollment['student_id']) && $edit_enrollment['student_id'] == $s['student_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label>Select Class:</label>
        <select name="class_id" required>
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= $c['class_id'] ?>" <?= (isset($edit_enrollment['class_id']) && $edit_enrollment['class_id'] == $c['class_id']) ? 'selected' : '' ?>>
                    Grade <?= $c['grade_level'] ?> - <?= htmlspecialchars($c['section']) ?> (<?= htmlspecialchars($c['academic_year']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <button type="submit" name="save_enrollment"><?= $edit_enrollment ? 'Update Enrollment' : 'Enroll Student' ?></button>
        <?php if ($edit_enrollment): ?>
            <a href="enroll.php" style="margin-left: 10px;">Cancel Edit</a>
        <?php endif; ?>
    </p>
</form>

<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px;">
    <h3>Current Enrollments</h3>
    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
        <a href="db_tools.php?action=export_data&table=all" class="btn-tool btn-tool-export">export all fields</a>
        <button type="button" class="btn-tool" style="background-color: #e53e3e; color: white; border-color: #c53030;" onclick="openReplaceImportModal('Replace All Database Data', 'all')">⚠️ replace import all</button>
    </div>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Student Name</th>
                <th>Class Assigned</th>
                <th>Academic Year</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($enrollments) > 0): ?>
                <?php foreach ($enrollments as $e): ?>
                    <tr>
                        <td><?= $e['enrollment_id'] ?></td>
                        <td><?= htmlspecialchars($e['student_name']) ?></td>
                        <td>Grade <?= $e['grade_level'] ?> - <?= htmlspecialchars($e['section']) ?></td>
                        <td><?= htmlspecialchars($e['academic_year']) ?></td>
                        <td>
                            <a href="enroll.php?edit=<?= $e['enrollment_id'] ?>">Edit</a> |
                            <a href="enroll.php?delete=<?= $e['enrollment_id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this enrollment?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No enrollments found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


</body>

</html>