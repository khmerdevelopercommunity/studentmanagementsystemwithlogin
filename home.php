<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$max_idle_seconds = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $max_idle_seconds)) {
    log_system_event($conn, $_SESSION['username'], 'SESSION_TIMEOUT_EXPIRED');
    session_unset();
    session_destroy();
    header("Location: index.php?expired=1");
    exit;
}
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Core Environment</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; text-align: center; background-color: #0f172a; color: #f8fafc; margin: 0; padding-top: 100px; }
        .card { background: #1e293b; display: inline-block; padding: 45px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); border: 1px solid #334155; }
        h1 { font-size: 48px; margin: 0 0 10px 0; color: #38bdf8; }
        p { color: #94a3b8; font-size: 16px; }
        .btn { display: inline-block; margin-top: 25px; padding: 12px 24px; background: #ef4444; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h1>hello</h1>
        <p>Secured Operational Space Entity: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
        <a href="logout.php" class="btn">Terminate Session Context</a>
    </div>
</body>
</html>