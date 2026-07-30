<?php
require_once 'db.php';

$action = $_GET['action'] ?? '';
$table  = $_GET['table'] ?? 'all';

// ============================================================================
// 1. EXPORT DATA ONLY (Single Table or All Tables)
// ============================================================================
if ($action === 'export_data') {
    $filename = ($table !== 'all' ? $table . "_data_" : "full_data_") . date('Y-m-d') . ".sql";
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $tables = ($table !== 'all') ? [$table] : $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    echo "-- Database Data Dump (" . ucfirst($table) . ")\n";
    echo "-- Exported on: " . date('Y-m-d H:i:s') . "\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $tbl) {
        $rows = $pdo->query("SELECT * FROM `$tbl`")->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            echo "-- Data for table `$tbl` --\n";
            foreach ($rows as $row) {
                $columns = array_keys($row);
                $escapedValues = array_map(function($val) use ($pdo) {
                    if ($val === null) return "NULL";
                    return $pdo->quote($val);
                }, array_values($row));

                echo "INSERT INTO `$tbl` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
            }
            echo "\n";
        }
    }

    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    exit;
}

// ============================================================================
// 2. IMPORT SQL FILE
// ============================================================================
if ($action === 'import' && isset($_FILES['sql_file'])) {
    $file = $_FILES['sql_file']['tmp_name'];

    if (!empty($file) && file_exists($file)) {
        $sql = file_get_contents($file);

        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
            $pdo->exec($sql);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

            $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            header("Location: {$referer}?msg=import_success");
        } catch (Exception $e) {
            $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            header("Location: {$referer}?msg=import_error");
        }
        exit;
    }
}
?>