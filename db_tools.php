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

    // Get all tables
    $tables = ($table !== 'all') ? [$table] : $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    echo "-- Database Full Backup\n";
    echo "-- Exported on: " . date('Y-m-d H:i:s') . "\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $tbl) {
        // Skip system tables (users, audit_logs) during export
        if ($table === 'all' && in_array($tbl, ['users', 'audit_logs'])) {
            continue;
        }
        
        try {
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
        } catch (PDOException $e) {
            // Skip tables that don't exist or have issues
            continue;
        }
    }

    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    exit;
}

// ============================================================================
// 2. REPLACE IMPORT - Clears all school tables and imports fresh data
//     PRESERVES: users, audit_logs (system tables)
//     REPLACES: All school tables (teachers, students, classes, etc.)
// ============================================================================
if ($action === 'replace_import' && isset($_FILES['sql_file'])) {
    $file = $_FILES['sql_file']['tmp_name'];
    $targetTable = $_POST['table_target'] ?? 'all';

    if (!empty($file) && file_exists($file)) {
        $sql = file_get_contents($file);

        try {
            // Disable foreign key checks temporarily
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

            // Get all tables that exist in the database
            $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            // System tables to ALWAYS preserve (login, registration, security)
            // These tables are NEVER touched during replace import
            $systemTables = [];
            if (in_array('users', $existingTables)) {
                $systemTables[] = 'users';
            }
            if (in_array('audit_logs', $existingTables)) {
                $systemTables[] = 'audit_logs';
            }
            
            // Tables to truncate (ALL tables EXCEPT system tables)
            $tablesToTruncate = array_diff($existingTables, $systemTables);
            
            // Truncate tables in reverse order to avoid foreign key issues
            foreach (array_reverse($tablesToTruncate) as $tbl) {
                try {
                    $pdo->exec("TRUNCATE TABLE `$tbl`;");
                    // Reset auto-increment counter for clean IDs
                    $pdo->exec("ALTER TABLE `$tbl` AUTO_INCREMENT = 1;");
                } catch (PDOException $e) {
                    // If table doesn't exist or can't be truncated, skip it
                    continue;
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