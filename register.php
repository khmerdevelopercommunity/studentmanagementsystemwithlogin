<?php
require 'db.php';

$message = "";
$status  = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security token validation failed.");
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (strlen($password) < 12 || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[^a-zA-Z0-9]/', $password)) {
        
        $message = "Password must be 12+ characters with upper/lowercase, numbers, and symbols.";
        $status = "error";
    } else if (!empty($username)) {
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Username is already taken.";
            $status = "error";
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                log_system_event($conn, $username, 'USER_REGISTRATION_SUCCESSFUL');
                $message = "Account created successfully.";
                $status = "success";
            } else {
                $message = "Registration failed.";
                $status = "error";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Management System</title>
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
        
        /* Register Container */
        .register-container {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }
        
        /* Register Box */
        .box { 
            background: #1e293b; 
            padding: clamp(30px, 6vw, 45px); 
            border-radius: 12px; 
            box-shadow: 0 8px 32px rgba(0,0,0,0.4); 
            border: 1px solid #334155;
            transition: all 0.3s ease;
        }
        
        /* Header */
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h2 { 
            color: #10b981; 
            font-size: clamp(24px, 5vw, 32px);
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .register-header p {
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
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        
        .form-group input::placeholder {
            color: #64748b;
        }
        
        /* Submit Button */
        .register-btn { 
            width: 100%; 
            padding: clamp(12px, 2vw, 15px); 
            background: linear-gradient(135deg, #059669, #10b981); 
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
        
        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .register-btn:active {
            transform: translateY(0);
        }
        
        /* Messages */
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
        .password-requirements {
            background: #0f172a;
            border-radius: 8px;
            padding: 12px 15px;
            margin-top: 8px;
            border: 1px solid #334155;
        }
        
        .password-requirements p {
            font-size: clamp(11px, 1vw, 12px);
            color: #94a3b8;
            margin-bottom: 6px;
            font-weight: 600;
        }
        
        .password-requirements ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .password-requirements ul li {
            font-size: clamp(11px, 1vw, 12px);
            color: #94a3b8;
            padding: 2px 0;
            padding-left: 20px;
            position: relative;
        }
        
        .password-requirements ul li::before {
            content: "❌";
            position: absolute;
            left: 0;
            top: 2px;
        }
        
        .password-requirements ul li.valid::before {
            content: "✅";
        }
        
        .password-requirements ul li.valid {
            color: #34d399;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            body {
                padding: 12px;
                align-items: flex-start;
                padding-top: 30px;
            }
            
            .box {
                padding: clamp(20px, 5vw, 30px);
            }
            
            .register-header h2 {
                font-size: 24px;
            }
            
            .form-group input {
                font-size: 16px;
                padding: 12px;
            }
            
            .register-btn {
                font-size: 16px;
                padding: 14px;
                min-height: 50px;
            }
        }
        
        @media (max-width: 360px) {
            body {
                padding: 8px;
                padding-top: 15px;
            }
            
            .box {
                padding: 16px;
            }
            
            .register-header h2 {
                font-size: 20px;
            }
            
            .form-group input {
                font-size: 14px;
                padding: 10px;
            }
            
            .password-requirements {
                padding: 8px 10px;
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
    <div class="register-container">
        <div class="box">
            <div class="register-header">
                <h2>📝 Register Account</h2>
                <p>Create your account to access the system</p>
            </div>
            
            <?php if ($status === "success"): ?>
                <div class="success">
                    ✅ <?= htmlspecialchars($message) ?> 
                    <a href="login.php" style="color: #38bdf8; font-weight: 600; display: inline-block; margin-top: 5px;">Sign In Now →</a>
                </div>
            <?php endif; ?>
            
            <?php if ($status === "error"): ?>
                <div class="error">❌ <?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="username">👤 Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                </div>
                
                <!-- Password Requirements -->
                <div class="password-requirements">
                    <p>📋 Password Requirements:</p>
                    <ul>
                        <li id="req-length">At least 12 characters</li>
                        <li id="req-upper">At least one uppercase letter (A-Z)</li>
                        <li id="req-lower">At least one lowercase letter (a-z)</li>
                        <li id="req-number">At least one number (0-9)</li>
                        <li id="req-symbol">At least one symbol (!@#$%^&* etc.)</li>
                    </ul>
                </div>
                
                <button type="submit" class="register-btn">✅ Create Account</button>
            </form>
            
            <div class="links">
                <a href="login.php">🔑 Back to Sign In</a>
            </div>
        </div>
    </div>
    
    <script>
        // Real-time password validation
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    
                    // Check requirements
                    const hasLength = password.length >= 12;
                    const hasUpper = /[A-Z]/.test(password);
                    const hasLower = /[a-z]/.test(password);
                    const hasNumber = /[0-9]/.test(password);
                    const hasSymbol = /[^a-zA-Z0-9]/.test(password);
                    
                    // Update UI
                    updateRequirement('req-length', hasLength);
                    updateRequirement('req-upper', hasUpper);
                    updateRequirement('req-lower', hasLower);
                    updateRequirement('req-number', hasNumber);
                    updateRequirement('req-symbol', hasSymbol);
                });
            }
            
            function updateRequirement(id, isValid) {
                const element = document.getElementById(id);
                if (element) {
                    if (isValid) {
                        element.classList.add('valid');
                    } else {
                        element.classList.remove('valid');
                    }
                }
            }
        });
    </script>
</body>
</html>