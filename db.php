<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "primary_school_db"; // YOUR ACTUAL DATABASE NAME

// MySQLi connection (Security/Auth system)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Authentication database connection failed.");
}

// PDO connection (School Management CRUD operations)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("School database connection failed: " . $e->getMessage());
}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// System Audit Event Logging
function log_system_event($conn, $username, $action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';
    $log_stmt = $conn->prepare("INSERT INTO audit_logs (username, action_performed, network_ip) VALUES (?, ?, ?)");
    $log_stmt->bind_param("sss", $username, $action, $ip);
    $log_stmt->execute();
    $log_stmt->close();
}

// Session Timeout Enforcement (15 Minutes idle time)
function check_session_timeout($conn) {
    $max_idle_seconds = 900;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $max_idle_seconds)) {
        log_system_event($conn, $_SESSION['username'] ?? 'UNKNOWN', 'SESSION_TIMEOUT_EXPIRED');
        session_unset();
        session_destroy();
        header("Location: login.php?expired=1");
        exit;
    }
    $_SESSION['last_activity'] = time();
}

// Authentication Check Guard (Protects pages)
function require_auth($conn) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    check_session_timeout($conn);
}
?>