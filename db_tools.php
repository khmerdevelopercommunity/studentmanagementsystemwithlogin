<?php
require_once 'db.php';

$action = $_GET['action'] ?? '';
$table  = $_GET['table'] ?? 'all';

// ============================================================================
// 1. EXPORT DATA (All Tables)
// ============================================================================
if ($action === 'export_data') {
    $filename = "full_database_backup_" . date('Y-m-d') . ".sql";
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $tables = ($table !== 'all') ? [$table] : $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    echo "-- Database Full Backup\n";
    echo "-- Exported on: " . date('Y-m-d H:i:s') . "\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $tbl) {
        // Skip system tables for export
        if ($table === 'all' && in_array($tbl, ['users', 'audit_logs'])) {
            continue;
        }
        
        $rows = $pdo->query("SELECT * FROM `$tbl`")->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            echo "-- Data for table `$tbl` --\n";
            foreach ($rows as $row) {
                $columns = array_keys($row);
                $escapedValues = array_map(function($val) use ($pdo) {
                    if ($val === null) return "NULL";
                    return $pdo->quote($val);
                }, array_values($row));

                // Use REPLACE INTO to handle duplicates gracefully
                echo "REPLACE INTO `$tbl` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
            }
            echo "\n";
        }
    }

    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    exit;
}

// ============================================================================
// 2. REPLACE IMPORT - Clears all tables and imports fresh data
// ============================================================================
if ($action === 'replace_import' && isset($_FILES['sql_file'])) {
    $file = $_FILES['sql_file']['tmp_name'];
    $targetTable = $_POST['table_target'] ?? 'all';

    if (!empty($file) && file_exists($file)) {
        $sql = file_get_contents($file);

        try {
            // Disable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

            // Get all tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            // Exclude system tables from being truncated (preserve users & audit_logs)
            $excludeTables = ['users', 'audit_logs'];
            
            if ($targetTable !== 'all') {
                // Only truncate the specific table if it exists and is not a system table
                if (in_array($targetTable, $tables) && !in_array($targetTable, $excludeTables)) {
                    $pdo->exec("TRUNCATE TABLE `$targetTable`;");
                    // Reset auto-increment for the specific table
                    $pdo->exec("ALTER TABLE `$targetTable` AUTO_INCREMENT = 1;");
                }
            } else {
                // Truncate all tables except system tables
                $tablesToTruncate = array_diff($tables, $excludeTables);
                
                // Truncate tables in reverse order to avoid FK issues
                foreach (array_reverse($tablesToTruncate) as $tbl) {
                    $pdo->exec("TRUNCATE TABLE `$tbl`;");
                    // Reset auto-increment for each table
                    $pdo->exec("ALTER TABLE `$tbl` AUTO_INCREMENT = 1;");
                }
            }

            // Convert any INSERT INTO to REPLACE INTO to avoid duplicate key errors
            $sql = preg_replace('/INSERT INTO/', 'REPLACE INTO', $sql);
            
            // Execute the import SQL
            $pdo->exec($sql);

            // Re-enable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

            $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            header("Location: {$referer}?msg=replace_import_success");
        } catch (Exception $e) {
            // Re-enable foreign key checks even on error
            try {
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
            } catch (Exception $inner) {
                // Ignore
            }
            $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            header("Location: {$referer}?msg=replace_import_error&error=" . urlencode($e->getMessage()));
        }
        exit;
    }
}
?>