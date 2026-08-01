<?php
require_once 'db.php';

if (isset($_POST['save_class'])) {
    $grade_level  = $_POST['grade_level'];
    $section      = $_POST['section'];
    $academic_yr  = $_POST['academic_year'];
    $teacher_id   = !empty($_POST['homeroom_teacher_id']) ? $_POST['homeroom_teacher_id'] : null;
    $id           = $_POST['class_id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE classes SET grade_level=?, section=?, academic_year=?, homeroom_teacher_id=? WHERE class_id=?");
        $stmt->execute([$grade_level, $section, $academic_yr, $teacher_id, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO classes (grade_level, section, academic_year, homeroom_teacher_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$grade_level, $section, $academic_yr, $teacher_id]);
    }
    header("Location: classes.php");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM classes WHERE class_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: classes.php");
    exit;
}

$edit_class = isset($_GET['edit']) ? $pdo->prepare("SELECT * FROM classes WHERE class_id = ?") : null;
if ($edit_class) {
    $edit_class->execute([$_GET['edit']]);
    $edit_class = $edit_class->fetch();
}

$teachers = $pdo->query("SELECT * FROM teachers ORDER BY first_name ASC")->fetchAll();
$classes  = $pdo->query("
    SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) AS teacher_name 
    FROM classes c 
    LEFT JOIN teachers t ON c.homeroom_teacher_id = t.teacher_id 
    ORDER BY c.class_id DESC
")->fetchAll();

include 'header.php';
?>

<h2>Step 3: Classes Management (Grades 1 to 6)</h2>

<h3><?= $edit_class ? 'Edit Class' : 'Add New Class' ?></h3>

<form action="classes.php" method="POST">
    <?php if ($edit_class): ?>
        <input type="hidden" name="class_id" value="<?= $edit_class['class_id'] ?>">
    <?php endif; ?>

    <p>
        <label>Grade Level:</label>
        <select name="grade_level" required>
            <option value="">-- Select Grade --</option>
            <?php for ($g = 1; $g <= 6; $g++): ?>
                <option value="<?= $g ?>" <?= (isset($edit_class['grade_level']) && $edit_class['grade_level'] == $g) ? 'selected' : '' ?>>Grade <?= $g ?></option>
            <?php endfor; ?>
        </select>
    </p>

    <p>
        <label>Section (e.g., A, B):</label>
        <input type="text" name="section" value="<?= $edit_class['section'] ?? '' ?>" required>
    </p>

    <p>
        <label>Academic Year:</label>
        <input type="text" name="academic_year" value="<?= $edit_class['academic_year'] ?? '2026-2027' ?>" required>
    </p>

    <p>
        <label>Homeroom Teacher:</label>
        <select name="homeroom_teacher_id">
            <option value="">-- Assign Homeroom Teacher --</option>
            <?php foreach ($teachers as $t): ?>
                <option value="<?= $t['teacher_id'] ?>" <?= (isset($edit_class['homeroom_teacher_id']) && $edit_class['homeroom_teacher_id'] == $t['teacher_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <button type="submit" name="save_class"><?= $edit_class ? 'Update Class' : 'Add Class' ?></button>
    </p>
</form>

<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px;">
    <h3>Classes List</h3>
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
                <th>Grade</th>
                <th>Section</th>
                <th>Academic Year</th>
                <th>Homeroom Teacher</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($classes as $c): ?>
                <tr>
                    <td><?= $c['class_id'] ?></td>
                    <td>Grade <?= $c['grade_level'] ?></td>
                    <td><?= htmlspecialchars($c['section']) ?></td>
                    <td><?= htmlspecialchars($c['academic_year']) ?></td>
                    <td><?= htmlspecialchars($c['teacher_name'] ?? 'Unassigned') ?></td>
                    <td>
                        <a href="classes.php?edit=<?= $c['class_id'] ?>">Edit</a> |
                        <a href="classes.php?delete=<?= $c['class_id'] ?>" class="btn-delete" onclick="return confirm('Delete class?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


</body>

</html>