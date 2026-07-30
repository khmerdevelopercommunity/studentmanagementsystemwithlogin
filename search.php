<?php
require_once 'db.php';

$rawQuery = trim($_GET['q'] ?? '');

$teachers = [];
$subjects = [];
$classes  = [];
$students = [];

if ($rawQuery !== '') {
    // Clean spaces for keyword check (e.g., "stuu dent" -> "stuudent")
    $cleanQuery = strtolower(str_replace(' ', '', $rawQuery));
    $words = array_filter(explode(' ', $rawQuery));

    // Helper function for multi-word SQL matching
    function getWordQuery($fields, $words) {
        $sqlParts = [];
        $params = [];
        foreach ($words as $word) {
            $wordTerm = "%$word%";
            $fieldParts = [];
            foreach ($fields as $field) {
                $fieldParts[] = "$field LIKE ?";
                $params[] = $wordTerm;
            }
            $sqlParts[] = "(" . implode(" OR ", $fieldParts) . ")";
        }
        return ['sql' => implode(" AND ", $sqlParts), 'params' => $params];
    }

    // 1. SEARCH TEACHERS
    if (strpos($cleanQuery, 'teacher') !== false) {
        $teachers = $pdo->query("SELECT * FROM teachers ORDER BY teacher_id DESC")->fetchAll();
    } else {
        $fields = ["first_name", "last_name", "REPLACE(CONCAT(first_name, last_name), ' ', '')", "email", "phone"];
        $qData = getWordQuery($fields, $words);
        $stmt = $pdo->prepare("SELECT * FROM teachers WHERE {$qData['sql']} ORDER BY teacher_id DESC");
        $stmt->execute($qData['params']);
        $teachers = $stmt->fetchAll();
    }

    // 2. SEARCH SUBJECTS
    if (strpos($cleanQuery, 'subject') !== false) {
        $subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_id DESC")->fetchAll();
    } else {
        $fields = ["subject_name", "REPLACE(subject_name, ' ', '')", "description"];
        $qData = getWordQuery($fields, $words);
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE {$qData['sql']} ORDER BY subject_id DESC");
        $stmt->execute($qData['params']);
        $subjects = $stmt->fetchAll();
    }

    // 3. SEARCH CLASSES
    if (strpos($cleanQuery, 'class') !== false || strpos($cleanQuery, 'grade') !== false) {
        $classes = $pdo->query("
            SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) AS teacher_name 
            FROM classes c 
            LEFT JOIN teachers t ON c.homeroom_teacher_id = t.teacher_id 
            ORDER BY c.class_id DESC
        ")->fetchAll();
    } else {
        $fields = ["c.section", "c.academic_year", "CONCAT('Grade', c.grade_level)", "CONCAT(t.first_name, t.last_name)"];
        $qData = getWordQuery($fields, $words);
        $stmt = $pdo->prepare("
            SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) AS teacher_name 
            FROM classes c 
            LEFT JOIN teachers t ON c.homeroom_teacher_id = t.teacher_id 
            WHERE {$qData['sql']}
            ORDER BY c.class_id DESC
        ");
        $stmt->execute($qData['params']);
        $classes = $stmt->fetchAll();
    }

    // 4. SEARCH STUDENTS
    if (strpos($cleanQuery, 'student') !== false) {
        $students = $pdo->query("SELECT * FROM students ORDER BY student_id DESC")->fetchAll();
    } else {
        $fields = ["first_name", "last_name", "REPLACE(CONCAT(first_name, last_name), ' ', '')", "medical_notes", "gender"];
        $qData = getWordQuery($fields, $words);
        $stmt = $pdo->prepare("SELECT * FROM students WHERE {$qData['sql']} ORDER BY student_id DESC");
        $stmt->execute($qData['params']);
        $students = $stmt->fetchAll();
    }
}

include 'header.php';
?>

<h2>Search Results for: "<em><?= htmlspecialchars($rawQuery) ?></em>"</h2>

<h3 class="search-section">Teachers (<?= count($teachers) ?> found)</h3>
<?php if (count($teachers) > 0): ?>
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($teachers as $t): ?>
                <tr>
                    <td><?= $t['teacher_id'] ?></td>
                    <td><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></td>
                    <td><?= htmlspecialchars($t['email']) ?></td>
                    <td><?= htmlspecialchars($t['phone'] ?? 'None') ?></td>
                    <td><a href="teachers.php?edit=<?= $t['teacher_id'] ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No teachers matched your query.</p>
<?php endif; ?>

<h3 class="search-section">Subjects (<?= count($subjects) ?> found)</h3>
<?php if (count($subjects) > 0): ?>
    <table>
        <thead><tr><th>ID</th><th>Subject Name</th><th>Description</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($subjects as $sub): ?>
                <tr>
                    <td><?= $sub['subject_id'] ?></td>
                    <td><?= htmlspecialchars($sub['subject_name']) ?></td>
                    <td><?= htmlspecialchars($sub['description'] ?? 'None') ?></td>
                    <td><a href="subjects.php?edit=<?= $sub['subject_id'] ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No subjects matched your query.</p>
<?php endif; ?>

<h3 class="search-section">Classes (<?= count($classes) ?> found)</h3>
<?php if (count($classes) > 0): ?>
    <table>
        <thead><tr><th>ID</th><th>Grade</th><th>Section</th><th>Academic Year</th><th>Homeroom Teacher</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($classes as $c): ?>
                <tr>
                    <td><?= $c['class_id'] ?></td>
                    <td>Grade <?= $c['grade_level'] ?></td>
                    <td><?= htmlspecialchars($c['section']) ?></td>
                    <td><?= htmlspecialchars($c['academic_year']) ?></td>
                    <td><?= htmlspecialchars($c['teacher_name'] ?? 'Unassigned') ?></td>
                    <td><a href="classes.php?edit=<?= $c['class_id'] ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No classes matched your query.</p>
<?php endif; ?>

<h3 class="search-section">Students (<?= count($students) ?> found)</h3>
<?php if (count($students) > 0): ?>
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>DOB</th><th>Gender</th><th>Medical Notes</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= $s['student_id'] ?></td>
                    <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                    <td><?= $s['dob'] ?></td>
                    <td><?= $s['gender'] ?></td>
                    <td><?= htmlspecialchars($s['medical_notes'] ?? 'None') ?></td>
                    <td><a href="students.php?edit=<?= $s['student_id'] ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No students matched your query.</p>
<?php endif; ?>

</body>
</html>