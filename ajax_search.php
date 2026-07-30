<?php
require_once 'db.php';

header('Content-Type: application/json');

$raw_q = trim($_GET['q'] ?? '');

if (strlen($raw_q) < 1) {
    echo json_encode([]);
    exit;
}

// 1. Remove spaces from the query (e.g. "stuu dent" becomes "stuudent")
$clean_q = str_replace(' ', '', $raw_q);

// 2. Split query by space into individual words (e.g. "John Smith" -> ["John", "Smith"])
$words = array_filter(explode(' ', $raw_q));

$searchTerm = "%$clean_q%";

$results = [
    'teachers' => [],
    'subjects' => [],
    'classes'  => [],
    'students' => []
];

// Helper to build flexible WHERE conditions for multiple words
function buildWordConditions($fields, $words) {
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

// --- SEARCH TEACHERS ---
$fields = ["first_name", "last_name", "REPLACE(CONCAT(first_name, last_name), ' ', '')", "email", "phone"];
$conds = buildWordConditions($fields, $words);
$stmt = $pdo->prepare("SELECT teacher_id, CONCAT(first_name, ' ', last_name) AS title, email AS sub FROM teachers WHERE {$conds['sql']} LIMIT 4");
$stmt->execute($conds['params']);
$results['teachers'] = $stmt->fetchAll();

// --- SEARCH SUBJECTS ---
$fields = ["subject_name", "REPLACE(subject_name, ' ', '')", "description"];
$conds = buildWordConditions($fields, $words);
$stmt = $pdo->prepare("SELECT subject_id, subject_name AS title, description AS sub FROM subjects WHERE {$conds['sql']} LIMIT 4");
$stmt->execute($conds['params']);
$results['subjects'] = $stmt->fetchAll();

// --- SEARCH CLASSES ---
$fields = ["section", "academic_year", "CONCAT('Grade', grade_level)"];
$conds = buildWordConditions($fields, $words);
$stmt = $pdo->prepare("SELECT class_id, CONCAT('Grade ', grade_level, ' - ', section) AS title, academic_year AS sub FROM classes WHERE {$conds['sql']} LIMIT 4");
$stmt->execute($conds['params']);
$results['classes'] = $stmt->fetchAll();

// --- SEARCH STUDENTS ---
$fields = ["first_name", "last_name", "REPLACE(CONCAT(first_name, last_name), ' ', '')", "medical_notes", "gender"];
$conds = buildWordConditions($fields, $words);
$stmt = $pdo->prepare("SELECT student_id, CONCAT(first_name, ' ', last_name) AS title, gender AS sub FROM students WHERE {$conds['sql']} LIMIT 4");
$stmt->execute($conds['params']);
$results['students'] = $stmt->fetchAll();

echo json_encode($results);
?>