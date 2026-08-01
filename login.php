<?php
require 'db.php';

// If already authenticated, bypass login and go directly to SMS
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        log_system_event($conn, 'ANONYMOUS', 'CSRF_VALIDATION_FAILURE');
        die("Security token validation failed.");
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $now      = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("SELECT id, password, login_attempts, lock_until FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $login_attempts, $lock_until);
        $stmt->fetch();

        if ($lock_until && $lock_until > $now) {
            log_system_event($conn, $username, 'LOGIN_REJECTED_ACCOUNT_LOCKED');
            $error = "Account locked due to multiple failed login attempts. Try again later.";
        } else {
            if (password_verify($password, $hashed_password)) {
                $reset_stmt = $conn->prepare("UPDATE users SET login_attempts = 0, lock_until = NULL WHERE id = ?");
                $reset_stmt->bind_param("i", $id);
                $reset_stmt->execute();
                $reset_stmt->close();

                session_regenerate_id(true);
                $_SESSION['user_id']       = $id;
                $_SESSION['username']      = $username;
                $_SESSION['last_activity'] = time();

                log_system_event($conn, $username, 'LOGIN_SUCCESSFUL');
                
                // --- PASS THROUGH THE WALL TO STUDENT MANAGEMENT SYSTEM ---
                header("Location: index.php");
                exit;
            } else {
                $login_attempts++;
                if ($login_attempts >= 5) {
                    $lock_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $lock_stmt = $conn->prepare("UPDATE users SET login_attempts = ?, lock_until = ? WHERE id = ?");
                    $lock_stmt->bind_param("isi", $login_attempts, $lock_time, $id);
                    log_system_event($conn, $username, 'ACCOUNT_TRIGGERED_LOCKOUT');
                } else {
                    $lock_stmt = $conn->prepare("UPDATE users SET login_attempts = ? WHERE id = ?");
                    $lock_stmt->bind_param("ii", $login_attempts, $id);
                    log_system_event($conn, $username, 'LOGIN_FAILED_WRONG_PASSWORD');
                }
                $lock_stmt->execute();
                $lock_stmt->close();

                $error = "Invalid username or password.";
            }
        }
    } else {
        log_system_event($conn, $username, 'LOGIN_FAILED_NONEXISTENT_USER');
        $error = "Invalid username or password.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Student Management System</title>
    <style>
        /* Reset & Base */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1a2332 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: #f8fafc;
        }
        
        /* Login Container */
        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }
        
        /* Login Box */
        .box { 
            background: #1e293b; 
            padding: clamp(30px, 6vw, 45px); 
            border-radius: 12px; 
            box-shadow: 0 8px 32px rgba(0,0,0,0.4); 
            border: 1px solid #334155;
            transition: all 0.3s ease;
        }
        
        /* Logo/Header */
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 { 
            color: #38bdf8; 
            font-size: clamp(24px, 5vw, 32px);
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: #94a3b8;
            font-size: clamp(13px, 1.5vw, 15px);
        }
        
        /* Form Elements */
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: clamp(13px, 1.2vw, 14px);
            color: #cbd5e0;
            margin-bottom: 5px;
        }
        
        .form-group input { 
            width: 100%; 
            padding: clamp(12px, 2vw, 14px); 
            box-sizing: border-box; 
            border: 2px solid #475569; 
            border-radius: 8px; 
            background: #0f172a; 
            color: #fff;
            font-size: clamp(16px, 1.2vw, 18px);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            -webkit-appearance: none;
            appearance: none;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
        }
        
        .form-group input::placeholder {
            color: #64748b;
        }
        
        /* Submit Button */
        .login-btn { 
            width: 100%; 
            padding: clamp(12px, 2vw, 15px); 
            background: linear-gradient(135deg, #0284c7, #0ea5e9); 
            border: none; 
            color: white; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 700; 
            font-size: clamp(16px, 1.2vw, 18px);
            margin-top: 8px;
            transition: all 0.3s ease;
            min-height: 48px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        /* Error Messages */
        .error { 
            color: #f87171; 
            background: rgba(248, 113, 113, 0.1); 
            border: 1px solid rgba(248, 113, 113, 0.2); 
            padding: clamp(10px, 1.5vw, 12px); 
            font-size: clamp(13px, 1.2vw, 14px); 
            border-radius: 8px; 
            margin-bottom: 18px; 
            text-align: center; 
        }
        
        .success { 
            color: #34d399; 
            background: rgba(52, 211, 153, 0.1); 
            border: 1px solid rgba(52, 211, 153, 0.2); 
            padding: clamp(10px, 1.5vw, 12px); 
            font-size: clamp(13px, 1.2vw, 14px); 
            border-radius: 8px; 
            margin-bottom: 18px; 
            text-align: center; 
        }
        
        /* Links */
        .links { 
            text-align: center; 
            margin-top: 22px; 
            padding-top: 18px;
            border-top: 1px solid #334155;
        }
        
        .links a { 
            color: #38bdf8; 
            text-decoration: none; 
            font-size: clamp(14px, 1.2vw, 15px);
            font-weight: 500;
            transition: color 0.2s;
            display: inline-block;
            padding: 8px 4px;
        }
        
        .links a:hover { 
            color: #7dd3fc; 
            text-decoration: underline;
        }
        
        /* Password Requirements */
        .password-hint {
            font-size: clamp(11px, 1vw, 12px);
            color: #94a3b8;
            margin-top: 6px;
            line-height: 1.5;
        }
        
        .password-hint span {
            display: inline-block;
            margin-right: 10px;
        }
        
        .password-hint .valid {
            color: #34d399;
        }
        
        .password-hint .invalid {
            color: #f87171;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            body {
                padding: 12px;
                align-items: flex-start;
                padding-top: 40px;
            }
            
            .box {
                padding: clamp(20px, 5vw, 30px);
            }
            
            .login-header h2 {
                font-size: 24px;
            }
            
            .form-group input {
                font-size: 16px;
                padding: 12px;
            }
            
            .login-btn {
                font-size: 16px;
                padding: 14px;
                min-height: 50px;
            }
        }
        
        @media (max-width: 360px) {
            body {
                padding: 8px;
                padding-top: 20px;
            }
            
            .box {
                padding: 16px;
            }
            
            .login-header h2 {
                font-size: 20px;
            }
            
            .form-group input {
                font-size: 14px;
                padding: 10px;
            }
        }
        
        @media (min-height: 800px) {
            body {
                align-items: center;
            }
        }
        
        /* Dark mode input autofill */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 30px #0f172a inset !important;
            -webkit-text-fill-color: #fff !important;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="box">
            <div class="login-header">
                <h2>🏫 System Sign In</h2>
                <p>Enter your credentials to access the system</p>
            </div>
            
            <?php if (isset($_GET['expired'])): ?>
                <div class="error">⏰ Session expired. Please log in again.</div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="username">👤 Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="login-btn">🔑 Access System</button>
            </form>
            
            <div class="links">
                <a href="register.php">📝 Register New Account</a>
            </div>
        </div>
    </div>
</body>
</html>