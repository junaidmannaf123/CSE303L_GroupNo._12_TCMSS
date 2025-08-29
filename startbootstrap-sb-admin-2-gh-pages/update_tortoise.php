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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = $_POST['name'] ?? '';
        $age = $_POST['age'] ?? '';
        $species = $_POST['species'] ?? '';
        
        if (empty($name) || empty($age) || empty($species)) {
            throw new Exception('All fields are required');
        }
        
        // Map species names to species IDs
        $species_map = [
            'Asian Giant Tortoise' => 'S1',
            'Arakan Forest Turtle' => 'S2',
            'Elongated Tortoise' => 'S3',
            'Keeled Box Turtle' => 'S4'
        ];
        
        $species_id = $species_map[$species] ?? 'S1';
        
        // Update tortoise
        $query = "UPDATE tbltortoise SET cname = :name, nage = :age, cspeciesid = :species WHERE ctortoiseid = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':id' => $tortoise_id,
            ':name' => $name,
            ':age' => $age,
            ':species' => $species_id
        ]);
        
        $message = "Tortoise updated successfully!";
        
        // Refresh tortoise data
        $stmt = $pdo->prepare("SELECT * FROM tbltortoise WHERE ctortoiseid = :id");
        $stmt->execute([':id' => $tortoise_id]);
        $tortoise = $stmt->fetch();
        
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
    <title>Update Tortoise</title>
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
                <h1 class="h4 text-gray-800">Update Tortoise - <?php echo htmlspecialchars($tortoise['cname']); ?></h1>
            </nav>

            <!-- Main Content -->
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card shadow">
                            <div class="card-header py-3 bg-warning text-white">
                                <h6 class="m-0 font-weight-bold">Edit Tortoise Information</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                                <?php endif; ?>
                                
                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="form-group">
                                        <label for="tortoise_id">Tortoise ID</label>
                                        <input type="text" class="form-control" id="tortoise_id" value="<?php echo htmlspecialchars($tortoise['ctortoiseid']); ?>" readonly>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="name">Tortoise Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($tortoise['cname']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="age">Age</label>
                                        <input type="number" class="form-control" id="age" name="age" value="<?php echo htmlspecialchars($tortoise['nage']); ?>" min="0" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="species">Species</label>
                                        <select class="form-control" id="species" name="species" required>
                                            <?php foreach ($speciesIdToName as $id => $name): ?>
                                                <option value="<?php echo $name; ?>" <?php echo ($id === $tortoise['cspeciesid']) ? 'selected' : ''; ?>>
                                                    <?php echo $name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="gender">Gender</label>
                                        <input type="text" class="form-control" id="gender" value="<?php echo htmlspecialchars($tortoise['cgender'] ?? ''); ?>" readonly>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="enclosure">Enclosure</label>
                                        <input type="text" class="form-control" id="enclosure" value="<?php echo htmlspecialchars($tortoise['cenclosureid'] ?? ''); ?>" readonly>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-warning">Update Tortoise</button>
                                        <a href="records_M.php" class="btn btn-secondary">Back to Records</a>
                                    </div>
                                </form>
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
