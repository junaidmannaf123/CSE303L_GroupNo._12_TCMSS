<?php
session_start();

// Clear any existing session when accessing login page directly
if (isset($_GET['clear']) || !isset($_POST['email'])) {
    // Clear session if explicitly requested or if not a POST request
    session_destroy();
    // Clear session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    // Start fresh session
    session_start();
}

require_once 'config/database.php';

$error_message = '';
$success_message = '';

// Check for logout and timeout messages
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $success_message = "You have been successfully logged out.";
}
if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $error_message = "Your session has expired. Please login again.";
}

// Only check for existing session if this is a POST request (actual login attempt)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['staff_id'])) {
    // Redirect based on role
    $role = $_SESSION['role'];
    switch ($role) {
        case 'Manager':
            header('Location: homePage.php');
            exit();
        case 'Tortoise Caretaker':
            header('Location: caretaker_dashboard.php');
            exit();
        case 'Veterinarian':
            header('Location: vetdashboard.php');
            exit();
        case 'Breeding Specialist':
            header('Location: BSDashboard.php');
            exit();
        case 'Inventory Manager':
            header('Location: inventory_dashboard.php');
            exit();
        case 'Environment Monitor':
            header('Location: Environment_Monitor.php');
            exit();
        default:
            header('Location: login.php');
            exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        try {
            // Check if login credentials table exists, if not create it
            $checkTable = $pdo->prepare("SHOW TABLES LIKE 'tbllogincredentials'");
            $checkTable->execute();
            
            if ($checkTable->rowCount() == 0) {
                // Create login credentials table
                $createTable = $pdo->prepare("
                    CREATE TABLE tbllogincredentials (
                        cstaffid varchar(8) NOT NULL,
                        cemail varchar(50) NOT NULL,
                        cpassword varchar(255) NOT NULL,
                        crole varchar(40) NOT NULL,
                        cstatus varchar(20) DEFAULT 'Active',
                        dcreated_date timestamp DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (cstaffid),
                        UNIQUE KEY (cemail)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
                ");
                $createTable->execute();
                
                // Insert default credentials for all staff members
                $insertCredentials = $pdo->prepare("
                    INSERT INTO tbllogincredentials (cstaffid, cemail, cpassword, crole) VALUES 
                    ('SM001', 'rahimk@tcc.org', 'manager123', 'Manager'),
                    ('SM002', 'anika@tcc.org', 'caretaker123', 'Tortoise Caretaker'),
                    ('SM003', 'jamil@tcc.org', 'breeding123', 'Breeding Specialist'),
                    ('SM004', 'farhana@tcc.org', 'vet123', 'Veterinarian'),
                    ('SM005', 'tanvir@tcc.org', 'inventory123', 'Inventory Manager'),
                    ('SM006', 'mehedi@tcc.org', 'environment123', 'Environment Monitor')
                ");
                $insertCredentials->execute();
                
                $success_message = "Login system initialized successfully! Default passwords have been set.";
            }
            
            // Now authenticate the user
            $stmt = $pdo->prepare("
                SELECT lc.cstaffid, lc.cemail, lc.cpassword, lc.crole, sm.cname
                FROM tbllogincredentials lc
                INNER JOIN tblstaffmember sm ON lc.cstaffid = sm.cstaffid
                WHERE lc.cemail = ? AND lc.cstatus = 'Active'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && $password === $user['cpassword']) {
                // Login successful - set session variables
                $_SESSION['staff_id'] = $user['cstaffid'];
                $_SESSION['staff_name'] = $user['cname'];
                $_SESSION['staff_email'] = $user['cemail'];
                $_SESSION['role'] = $user['crole'];
                $_SESSION['login_time'] = time();
                
                // Redirect based on role
                switch ($user['crole']) {
                    case 'Manager':
                        header('Location: homePage.php');
                        exit();
                    case 'Tortoise Caretaker':
                        header('Location: caretaker_dashboard.php');
                        exit();
                    case 'Veterinarian':
                        header('Location: vetdashboard.php');
                        exit();
                    case 'Breeding Specialist':
                        header('Location: BSDashboard.php');
                        exit();
                                                case 'Inventory Manager':
                        header('Location: inventory_dashboard.php');
                        exit();
                    case 'Environment Monitor':
                        header('Location: Environment_Monitor.php');
                        exit();
                    default:
                        header('Location: login.php');
                        exit();
                }
            } else {
                $error_message = "Invalid email or password. Please try again.";
            }
            
        } catch(PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Tortoise Center</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="css/sb-admin-2.min.css" rel="stylesheet">
  <style>
    body, html {
      height: 100%;
      background: url('https://cdn.pixabay.com/photo/2021/02/01/10/00/tortoise-5968739_1280.jpg') no-repeat center center fixed;
      background-size: cover;
    }
    .login-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      border-radius: 1.5rem;
      box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15);
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
    }
    .logo {
      font-size: 2.5rem;
      color: #1cc88a;
    }
    .credentials-info {
      background: rgba(28, 200, 138, 0.1);
      border: 1px solid rgba(28, 200, 138, 0.3);
      border-radius: 0.5rem;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    .role-badge {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.75rem;
      font-weight: bold;
      margin-left: 0.5rem;
    }
    .role-manager { background: #e3f2fd; color: #1976d2; }
    .role-caretaker { background: #e8f5e8; color: #388e3c; }
    .role-vet { background: #fff3e0; color: #f57c00; }
    .role-breeding { background: #f3e5f5; color: #7b1fa2; }
    .role-inventory { background: #e0f2f1; color: #00796b; }
    .role-environment { background: #fce4ec; color: #c2185b; }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="card p-4 col-md-6 col-lg-5">
      <div class="text-center mb-4">
        <span class="logo"><i class="fas fa-turtle"></i></span>
        <h2 class="font-weight-bold mt-2 text-success">TCCMS Login</h2>
        <p class="text-muted">Tortoise Conservation Center Management System</p>
      </div>
      
      <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
          <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
          </button>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
          <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
          </button>
        </div>
      <?php endif; ?>
      
      <form method="POST">
        <div class="form-group">
          <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
          <input type="email" class="form-control" id="email" name="email" 
                 placeholder="Enter your email address" required 
                 value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        <div class="form-group">
          <label for="password"><i class="fas fa-lock"></i> Password</label>
          <input type="password" class="form-control" id="password" name="password" 
                 placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn btn-success btn-block font-weight-bold">
          <i class="fas fa-sign-in-alt"></i> Login
        </button>
      </form>
      
      <div class="credentials-info mt-4">
        <h6 class="text-success mb-2"><i class="fas fa-info-circle"></i> Demo Credentials</h6>
        <div class="small">
          <div class="mb-1">
            <strong>Manager:</strong> rahimk@tcc.org / manager123
            <span class="role-badge role-manager">Manager</span>
          </div>
          <div class="mb-1">
            <strong>Caretaker:</strong> anika@tcc.org / caretaker123
            <span class="role-badge role-caretaker">Caretaker</span>
          </div>
          <div class="mb-1">
            <strong>Veterinarian:</strong> farhana@tcc.org / vet123
            <span class="role-badge role-vet">Veterinarian</span>
          </div>
          <div class="mb-1">
            <strong>Breeding Specialist:</strong> jamil@tcc.org / breeding123
            <span class="role-badge role-breeding">Breeding</span>
          </div>
          <div class="mb-1">
            <strong>Inventory Manager:</strong> tanvir@tcc.org / inventory123
            <span class="role-badge role-inventory">Inventory</span>
          </div>
          <div class="mb-1">
            <strong>Environment Monitor:</strong> mehedi@tcc.org / environment123
            <span class="role-badge role-environment">Environment</span>
          </div>
        </div>
      </div>
      
      <div class="text-center mt-3">
        <a href="forgot-password.html" class="small">Forgot Password?</a>
      </div>
      <div class="text-center mt-2">
        <span class="small">Don't have an account?</span>
        <a href="register.html" class="font-weight-bold">Contact Administrator</a>
      </div>
      <div class="text-center mt-2">
        <a href="login.php?clear=1" class="small text-muted">Clear Session & Start Fresh</a>
      </div>
    </div>
  </div>
  
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="js/sb-admin-2.min.js"></script>
</body>
</html>
