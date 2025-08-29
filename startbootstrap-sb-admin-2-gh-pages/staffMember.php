<?php
require_once 'config/database.php';

$message = '';
$error = '';
$search = $_GET['search'] ?? '';

// Handle form submission for adding new staff member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff'])) {
    try {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $street = $_POST['street'] ?? '';
        $house_number = $_POST['house_number'] ?? '';
        $city = $_POST['city'] ?? '';
        $zip = $_POST['zip'] ?? '';
        $role_type = $_POST['role_type'] ?? '';
        $shift = $_POST['shift'] ?? '';
        
        if (empty($name) || empty($email) || empty($phone) || empty($role_type) || empty($shift)) {
            throw new Exception('Name, Email, Phone, Role, and Shift are required');
        }
        
        // Generate new staff ID
        $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(cstaffid, 3) AS UNSIGNED)) as max_id FROM tblstaffmember");
        $result = $stmt->fetch();
        $new_id = 'SM' . str_pad(($result['max_id'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
        
        // Insert new staff member
        $query = "INSERT INTO tblstaffmember (cstaffid, cname, cemail, cphone, cstreet, chousenumber, ccity, czip, crole_type, cshift) VALUES (:id, :name, :email, :phone, :street, :house_number, :city, :zip, :role_type, :shift)";
        $stmt = $pdo->prepare($query);
        $insert_result = $stmt->execute([
            ':id' => $new_id,
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':street' => $street,
            ':house_number' => $house_number,
            ':city' => $city,
            ':zip' => $zip,
            ':role_type' => $role_type,
            ':shift' => $shift
        ]);
        
        if ($insert_result) {
            $message = "Staff member added successfully with ID: $new_id";
            $_POST = array(); // Clear form
        } else {
            throw new Exception('Failed to insert staff member into database');
        }
        
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle staff member deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    try {
        $staff_id = $_GET['delete'];
        
        // Check for foreign key constraints
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tbltortoise WHERE cstaffid = :id");
        $stmt->execute([':id' => $staff_id]);
        $tortoise_count = $stmt->fetch()['count'];
        
        if ($tortoise_count > 0) {
            throw new Exception('Cannot delete staff member: They are assigned to tortoises');
        }
        
        // Delete staff member
        $query = "DELETE FROM tblstaffmember WHERE cstaffid = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id' => $staff_id]);
        
        $message = "Staff member deleted successfully!";
        
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

try {
    // Build the query with search functionality
    $query = "SELECT * FROM tblstaffmember";
    $params = [];
    
    if (!empty($search)) {
        $query .= " WHERE cname LIKE :search OR cemail LIKE :search OR crole_type LIKE :search OR ccity LIKE :search";
        $params[':search'] = "%$search%";
    }
    
    $query .= " ORDER BY cstaffid";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $staff_members = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
    $staff_members = [];
}

// Get role statistics
try {
    $role_stats = $pdo->query("SELECT crole_type, COUNT(*) as count FROM tblstaffmember GROUP BY crole_type")->fetchAll();
} catch(PDOException $e) {
    $role_stats = [];
}

// Get shift statistics
try {
    $shift_stats = $pdo->query("SELECT cshift, COUNT(*) as count FROM tblstaffmember GROUP BY cshift")->fetchAll();
} catch(PDOException $e) {
    $shift_stats = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Management - Tortoise Conservation Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .staff-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .staff-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .role-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        
        .shift-badge {
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        
        .form-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 15px;
            color: white;
        }
    </style>
</head>
<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="homePage.php">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-turtle"></i>
            </div>
            <div class="sidebar-brand-text mx-3">Admin</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item">
            <a class="nav-link" href="homePage.php">
                <i class="fas fa-fw fa-home"></i>
                <span>Home</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="records_M.php">
                <i class="fas fa-fw fa-list"></i>
                <span>Tortoise Records</span>
            </a>
        </li>
        <li class="nav-item active">
            <a class="nav-link" href="staffMember.php">
                <i class="fas fa-fw fa-users"></i>
                <span>Staff Management</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="assignTasks.php">
                <i class="fas fa-fw fa-tasks"></i>
                <span>Assign Task</span>
            </a>
        </li>
    </ul>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h3 text-gray-800">
                    <i class="fas fa-users text-primary"></i> Staff Management
                </h1>
            </nav>

            <!-- Main Content -->
            <div class="container-fluid">

                <!-- Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Staff</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($staff_members); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Roles</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($role_stats); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Morning Shift</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $morning_count = 0;
                                            foreach ($shift_stats as $stat) {
                                                if ($stat['cshift'] === 'Morning') {
                                                    $morning_count = $stat['count'];
                                                    break;
                                                }
                                            }
                                            echo $morning_count;
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-sun fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Evening Shift</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $evening_count = 0;
                                            foreach ($shift_stats as $stat) {
                                                if ($stat['cshift'] === 'Evening') {
                                                    $evening_count = $stat['count'];
                                                    break;
                                                }
                                            }
                                            echo $evening_count;
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-moon fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Staff Member Form -->
                <div class="card shadow mb-4 form-section">
                    <div class="card-header py-3 text-white border-0">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-user-plus"></i> Add New Staff Member
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name" class="text-white"><strong>Full Name *</strong></label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="email" class="text-white"><strong>Email *</strong></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="phone" class="text-white"><strong>Phone *</strong></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="street" class="text-white"><strong>Street</strong></label>
                                        <input type="text" class="form-control" id="street" name="street" value="<?php echo htmlspecialchars($_POST['street'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="house_number" class="text-white"><strong>House #</strong></label>
                                        <input type="text" class="form-control" id="house_number" name="house_number" value="<?php echo htmlspecialchars($_POST['house_number'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="city" class="text-white"><strong>City</strong></label>
                                        <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="zip" class="text-white"><strong>ZIP Code</strong></label>
                                        <input type="text" class="form-control" id="zip" name="zip" value="<?php echo htmlspecialchars($_POST['zip'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="role_type" class="text-white"><strong>Role *</strong></label>
                                        <select class="form-control" id="role_type" name="role_type" required>
                                            <option value="">Select Role</option>
                                            <option value="Manager" <?php echo ($_POST['role_type'] ?? '') === 'Manager' ? 'selected' : ''; ?>>Manager</option>
                                            <option value="Tortoise Caretaker" <?php echo ($_POST['role_type'] ?? '') === 'Tortoise Caretaker' ? 'selected' : ''; ?>>Tortoise Caretaker</option>
                                            <option value="Breeding Specialist" <?php echo ($_POST['role_type'] ?? '') === 'Breeding Specialist' ? 'selected' : ''; ?>>Breeding Specialist</option>
                                            <option value="Veterinarian" <?php echo ($_POST['role_type'] ?? '') === 'Veterinarian' ? 'selected' : ''; ?>>Veterinarian</option>
                                            <option value="Inventory Manager" <?php echo ($_POST['role_type'] ?? '') === 'Inventory Manager' ? 'selected' : ''; ?>>Inventory Manager</option>
                                            <option value="Environment Monitor" <?php echo ($_POST['role_type'] ?? '') === 'Environment Monitor' ? 'selected' : ''; ?>>Environment Monitor</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="shift" class="text-white"><strong>Shift *</strong></label>
                                        <select class="form-control" id="shift" name="shift" required>
                                            <option value="">Select Shift</option>
                                            <option value="Morning" <?php echo ($_POST['shift'] ?? '') === 'Morning' ? 'selected' : ''; ?>>Morning</option>
                                            <option value="Evening" <?php echo ($_POST['shift'] ?? '') === 'Evening' ? 'selected' : ''; ?>>Evening</option>
                                            <option value="Night" <?php echo ($_POST['shift'] ?? '') === 'Night' ? 'selected' : ''; ?>>Night</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-8 d-flex align-items-end">
                                    <div class="form-group mb-0">
                                        <button type="submit" name="add_staff" class="btn btn-light btn-lg">
                                            <i class="fas fa-user-plus"></i> Add Staff Member
                                        </button>
                                        <button type="reset" class="btn btn-outline-light btn-lg ml-2">
                                            <i class="fas fa-undo"></i> Reset Form
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="row mb-3">
                    <div class="col-md-6 mx-auto">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, email, role, or city..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary ml-2">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="staffMember.php" class="btn btn-secondary ml-2">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Staff Members Grid -->
                <div class="row">
                    <?php if (empty($staff_members)): ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No staff members found</h4>
                                <?php if (!empty($search)): ?>
                                    <p>No results for "<?php echo htmlspecialchars($search); ?>"</p>
                                    <a href="staffMember.php" class="btn btn-primary">View All Staff</a>
                                <?php else: ?>
                                    <p>No staff members in the database yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($staff_members as $staff): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card staff-card shadow h-100">
                                    <div class="card-header bg-gradient-primary text-white text-center py-3">
                                        <h5 class="mb-0">
                                            <i class="fas fa-user-circle fa-2x mb-2"></i><br>
                                            <?php echo htmlspecialchars($staff['cname']); ?>
                                        </h5>
                                        <small class="text-light">ID: <?php echo htmlspecialchars($staff['cstaffid']); ?></small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <h6 class="text-primary mb-2">
                                                    <i class="fas fa-envelope"></i> Contact Info
                                                </h6>
                                                <p class="mb-1">
                                                    <strong>Email:</strong> <?php echo htmlspecialchars($staff['cemail']); ?>
                                                </p>
                                                <p class="mb-1">
                                                    <strong>Phone:</strong> <?php echo htmlspecialchars($staff['cphone']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <h6 class="text-success mb-2">
                                                    <i class="fas fa-map-marker-alt"></i> Address
                                                </h6>
                                                <p class="mb-1">
                                                    <?php echo htmlspecialchars($staff['chousenumber'] ?? ''); ?> 
                                                    <?php echo htmlspecialchars($staff['cstreet'] ?? ''); ?>
                                                </p>
                                                <p class="mb-1">
                                                    <?php echo htmlspecialchars($staff['ccity'] ?? ''); ?>, 
                                                    <?php echo htmlspecialchars($staff['czip'] ?? ''); ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-6">
                                                <span class="role-badge badge badge-primary">
                                                    <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($staff['crole_type']); ?>
                                                </span>
                                            </div>
                                            <div class="col-6">
                                                <span class="shift-badge badge badge-<?php echo $staff['cshift'] === 'Morning' ? 'warning' : ($staff['cshift'] === 'Evening' ? 'info' : 'dark'); ?>">
                                                    <i class="fas fa-<?php echo $staff['cshift'] === 'Morning' ? 'sun' : ($staff['cshift'] === 'Evening' ? 'moon' : 'star'); ?>"></i> 
                                                    <?php echo htmlspecialchars($staff['cshift']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light">
                                        <div class="btn-group w-100" role="group">
                                            <a href="update_staff.php?id=<?php echo urlencode($staff['cstaffid']); ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="?delete=<?php echo urlencode($staff['cstaffid']); ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this staff member?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div> <!-- /.container-fluid -->
        </div> <!-- /.content -->
    </div> <!-- /#content-wrapper -->
</div> <!-- /#wrapper -->

<!-- Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>

</body>
</html>
