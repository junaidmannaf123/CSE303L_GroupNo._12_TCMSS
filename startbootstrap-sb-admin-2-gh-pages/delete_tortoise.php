<?php
require_once 'config/database.php';

$message = '';
$error = '';
$tortoise = null;

// Get tortoise ID from URL
$tortoise_id = $_GET['id'] ?? '';

if (empty($tortoise_id)) {
    header('Location: records_M.php');
    exit;
}

// Fetch tortoise data
try {
    $stmt = $pdo->prepare("SELECT * FROM tbltortoise WHERE ctortoiseid = :id");
    $stmt->execute([':id' => $tortoise_id]);
    $tortoise = $stmt->fetch();
    
    if (!$tortoise) {
        header('Location: records_M.php');
        exit;
    }
} catch(Exception $e) {
    $error = 'Error fetching tortoise: ' . $e->getMessage();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Check for foreign key constraints
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tblbreedingrecord WHERE ctortoiseid = :id");
        $stmt->execute([':id' => $tortoise_id]);
        $breeding_count = $stmt->fetch()['count'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tbltortoisemeasurement WHERE ctortoiseid = :id");
        $stmt->execute([':id' => $tortoise_id]);
        $measurement_count = $stmt->fetch()['count'];
        
        if ($breeding_count > 0 || $measurement_count > 0) {
            throw new Exception('Cannot delete tortoise: Related records exist in breeding or measurement tables');
        }
        
        // Delete tortoise
        $query = "DELETE FROM tbltortoise WHERE ctortoiseid = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id' => $tortoise_id]);
        
        $message = "Tortoise deleted successfully!";
        
        // Redirect after successful deletion
        header("Refresh: 2; URL=records_M.php");
        
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

// Get species name from ID
$speciesIdToName = [
    'S1' => 'Asian Giant Tortoise',
    'S2' => 'Arakan Forest Turtle',
    'S3' => 'Elongated Tortoise',
    'S4' => 'Keeled Box Turtle'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Tortoise</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="homepage.html">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-turtle"></i>
            </div>
            <div class="sidebar-brand-text mx-3">Admin</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item">
            <a class="nav-link" href="homepage.html">
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
        <li class="nav-item">
            <a class="nav-link" href="create_tortoise.php">
                <i class="fas fa-fw fa-plus"></i>
                <span>Add Tortoise</span>
            </a>
        </li>
    </ul>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h4 text-gray-800">Delete Tortoise - <?php echo htmlspecialchars($tortoise['cname']); ?></h1>
            </nav>

            <!-- Main Content -->
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card shadow">
                            <div class="card-header py-3 bg-danger text-white">
                                <h6 class="m-0 font-weight-bold">Confirm Deletion</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                                    <p>Redirecting to records page...</p>
                                <?php else: ?>
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                    <?php endif; ?>

                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Warning:</strong> This action cannot be undone!
                                    </div>

                                    <h5>Tortoise Details:</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>ID:</th>
                                            <td><?php echo htmlspecialchars($tortoise['ctortoiseid']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Name:</th>
                                            <td><?php echo htmlspecialchars($tortoise['cname']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Age:</th>
                                            <td><?php echo htmlspecialchars($tortoise['nage']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Gender:</th>
                                            <td><?php echo htmlspecialchars($tortoise['cgender'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Species:</th>
                                            <td><?php echo htmlspecialchars($speciesIdToName[$tortoise['cspeciesid']] ?? $tortoise['cspeciesid']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Enclosure:</th>
                                            <td><?php echo htmlspecialchars($tortoise['cenclosureid'] ?? 'N/A'); ?></td>
                                        </tr>
                                    </table>

                                    <form method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete this tortoise? This action cannot be undone!');">
                                        <div class="form-group">
                                            <button type="submit" name="confirm_delete" class="btn btn-danger">
                                                <i class="fas fa-trash"></i> Confirm Delete
                                            </button>
                                            <a href="records_M.php" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i> Cancel
                                            </a>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>

</body>
</html>
