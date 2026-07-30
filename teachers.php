<?php
require_once 'db.php';

// Save or Update Teacher
if (isset($_POST['save_teacher'])) {
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $phone      = $_POST['phone'];
    $id         = $_POST['teacher_id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE teachers SET first_name=?, last_name=?, email=?, phone=? WHERE teacher_id=?");
        $stmt->execute([$first_name, $last_name, $email, $phone, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO teachers (first_name, last_name, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $phone]);
    }
    header("Location: teachers.php");
    exit;
}

// Delete Teacher
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM teachers WHERE teacher_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: teachers.php");
    exit;
}

// Fetch edit data
$edit_teacher = isset($_GET['edit']) ? $pdo->prepare("SELECT * FROM teachers WHERE teacher_id = ?") : null;
if ($edit_teacher) { 
    $edit_teacher->execute([$_GET['edit']]); 
    $edit_teacher = $edit_teacher->fetch(); 
}

$teachers = $pdo->query("SELECT * FROM teachers ORDER BY teacher_id DESC")->fetchAll();

include 'header.php';
?>

<h2>Step 1: Teachers Management</h2>

<h3><?= $edit_teacher ? 'Edit Teacher' : 'Add New Teacher' ?></h3>

<form action="teachers.php" method="POST">
    <?php if ($edit_teacher): ?>
        <input type="hidden" name="teacher_id" value="<?= $edit_teacher['teacher_id'] ?>">
    <?php endif; ?>

    <p>
        <label>First Name:</label>
        <input type="text" name="first_name" value="<?= $edit_teacher['first_name'] ?? '' ?>" required>
    </p>
    <p>
        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?= $edit_teacher['last_name'] ?? '' ?>" required>
    </p>
    <p>
        <label>Email:</label>
        <input type="email" name="email" value="<?= $edit_teacher['email'] ?? '' ?>" required>
    </p>
    <p>
        <label>Phone:</label>
        <input type="text" name="phone" value="<?= $edit_teacher['phone'] ?? '' ?>">
    </p>
    <p>
        <button type="submit" name="save_teacher"><?= $edit_teacher ? 'Update Teacher' : 'Add Teacher' ?></button>
    </p>
</form>

<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px;">
    <h3>Teachers List</h3>
    <div style="display: flex; gap: 6px;">
        <a href="db_tools.php?action=export_data&table=teachers" class="btn-tool btn-tool-export">export teacher</a>
        <a href="db_tools.php?action=export_data&table=all" class="btn-tool btn-tool-export">export all fields</a>
        <button type="button" class="btn-tool btn-tool-import" onclick="openImportModal('Import Teachers Data', 'teachers')">import teacher</button>
        <button type="button" class="btn-tool btn-tool-import" onclick="openImportModal('Import All Database Data', 'all')">import all fields</button>
    </div>
</div>

<table>
    <thead>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach ($teachers as $t): ?>
            <tr>
                <td><?= $t['teacher_id'] ?></td>
                <td><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></td>
                <td><?= htmlspecialchars($t['email']) ?></td>
                <td><?= htmlspecialchars($t['phone'] ?? 'None') ?></td>
                <td>
                    <a href="teachers.php?edit=<?= $t['teacher_id'] ?>">Edit</a> | 
                    <a href="teachers.php?delete=<?= $t['teacher_id'] ?>" class="btn-delete" onclick="return confirm('Delete this teacher?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>