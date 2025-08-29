<?php
require_once 'config/database.php';

$message = '';
$error = '';
$search = $_GET['search'] ?? '';

// Handle form submission for adding new tortoise
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tortoise'])) {
    try {
        $name = $_POST['name'] ?? '';
        $age = $_POST['age'] ?? '';
        $species = $_POST['species'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $enclosure = $_POST['enclosure'] ?? '';
        
        if (empty($name) || empty($age) || empty($species) || empty($gender) || empty($enclosure)) {
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
        
        // Generate new tortoise ID
        $stmt = $pdo->query("SELECT MAX(CAST(ctortoiseid AS UNSIGNED)) as max_id FROM tbltortoise");
        $result = $stmt->fetch();
        $new_id = str_pad(($result['max_id'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
        
        // Insert new tortoise
        $query = "INSERT INTO tbltortoise (ctortoiseid, cname, nage, cgender, cenclosureid, cspeciesid) VALUES (:id, :name, :age, :gender, :enclosure, :species)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':id' => $new_id,
            ':name' => $name,
            ':age' => $age,
            ':gender' => $gender,
            ':enclosure' => $enclosure,
            ':species' => $species_id
        ]);
        
        $message = "Tortoise added successfully with ID: $new_id";
        
        // Clear form data after successful submission
        $_POST = array();
        
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

try {
    // Build the query with search functionality
    $query = "
        SELECT 
            t.ctortoiseid,
            t.cname,
            t.nage,
            t.cgender,
            t.cenclosureid,
            t.cspeciesid,
            s.ccommonname as species_name,
            s.cscientificname as scientific_name,
            e.cenclosuretype,
            e.clocation,
            e.csize
        FROM tbltortoise t
        LEFT JOIN tblspecies s ON t.cspeciesid = s.cspeciesid
        LEFT JOIN tblenclosure e ON t.cenclosureid = e.cenclosureid
    ";
    
    $params = [];
    
    if (!empty($search)) {
        $query .= " WHERE t.cname LIKE :search OR t.ctortoiseid LIKE :search OR s.ccommonname LIKE :search";
        $params[':search'] = "%$search%";
    }
    
    $query .= " ORDER BY t.ctortoiseid";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $tortoises = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
    $tortoises = [];
}

// Get species name mapping
$speciesIdToName = [
    'S1' => 'Asian Giant Tortoise',
    'S2' => 'Arakan Forest Turtle',
    'S3' => 'Elongated Tortoise',
    'S4' => 'Keeled Box Turtle'
];

// Get available enclosures
try {
    $enclosure_stmt = $pdo->query("SELECT cenclosureid, clocation, cenclosuretype FROM tblenclosure ORDER BY cenclosureid");
    $enclosures = $enclosure_stmt->fetchAll();
} catch(PDOException $e) {
    $enclosures = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tortoise Detailed Records</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="css/sb-admin-2.min.css" rel="stylesheet">
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
    <li class="nav-item active">
      <a class="nav-link" href="records_M.php">
        <i class="fas fa-fw fa-list"></i>
        <span>Tortoise Records</span>
      </a>
    </li>
    <li class="nav-item">
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
        <h1 class="h4 text-gray-800">Tortoise Detailed Records</h1>
      </nav>

      <!-- Main Content -->
      <div class="container-fluid">
        
        <!-- Add Tortoise Form -->
        <div class="card shadow mb-4">
          <div class="card-header py-3 bg-success text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-plus"></i> Add New Tortoise</h6>
          </div>
          <div class="card-body">
            <?php if ($message): ?>
              <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="name">Tortoise Name *</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                  </div>
                </div>
                
                <div class="col-md-2">
                  <div class="form-group">
                    <label for="age">Age (years) *</label>
                    <input type="number" class="form-control" id="age" name="age" value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>" min="0" max="200" required>
                  </div>
                </div>
                
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="species">Species *</label>
                    <select class="form-control" id="species" name="species" required>
                      <option value="">Select Species</option>
                      <option value="Asian Giant Tortoise" <?php echo ($_POST['species'] ?? '') === 'Asian Giant Tortoise' ? 'selected' : ''; ?>>Asian Giant Tortoise</option>
                      <option value="Arakan Forest Turtle" <?php echo ($_POST['species'] ?? '') === 'Arakan Forest Turtle' ? 'selected' : ''; ?>>Arakan Forest Turtle</option>
                      <option value="Elongated Tortoise" <?php echo ($_POST['species'] ?? '') === 'Elongated Tortoise' ? 'selected' : ''; ?>>Elongated Tortoise</option>
                      <option value="Keeled Box Turtle" <?php echo ($_POST['species'] ?? '') === 'Keeled Box Turtle' ? 'selected' : ''; ?>>Keeled Box Turtle</option>
                    </select>
                  </div>
                </div>
                
                <div class="col-md-2">
                  <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select class="form-control" id="gender" name="gender" required>
                      <option value="">Select Gender</option>
                      <option value="male" <?php echo ($_POST['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                      <option value="female" <?php echo ($_POST['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                      <option value="juvenile" <?php echo ($_POST['gender'] ?? '') === 'juvenile' ? 'selected' : ''; ?>>Juvenile</option>
                    </select>
                  </div>
                </div>
                
                <div class="col-md-2">
                  <div class="form-group">
                    <label for="enclosure">Enclosure *</label>
                    <select class="form-control" id="enclosure" name="enclosure" required>
                      <option value="">Select Enclosure</option>
                      <?php foreach ($enclosures as $enc): ?>
                        <option value="<?php echo htmlspecialchars($enc['cenclosureid']); ?>" <?php echo ($_POST['enclosure'] ?? '') === $enc['cenclosureid'] ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($enc['cenclosureid']); ?> - <?php echo htmlspecialchars($enc['clocation']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
              
              <div class="form-group">
                <button type="submit" name="add_tortoise" class="btn btn-success">
                  <i class="fas fa-plus"></i> Add Tortoise
                </button>
                <button type="reset" class="btn btn-secondary">
                  <i class="fas fa-undo"></i> Reset Form
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="row mb-3">
          <div class="col-md-6 mx-auto">
            <form method="GET" class="d-flex">
              <input type="text" name="search" class="form-control" placeholder="Search by name, ID, or species..." value="<?php echo htmlspecialchars($search); ?>">
              <button type="submit" class="btn btn-primary ml-2"><i class="fas fa-search"></i> Search</button>
              <?php if (!empty($search)): ?>
                <a href="records_M.php" class="btn btn-secondary ml-2"><i class="fas fa-times"></i> Clear</a>
              <?php endif; ?>
            </form>
          </div>
        </div>

        <?php if ($error && !isset($_POST['add_tortoise'])): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Tortoise Table Card -->
        <div class="card shadow mb-4">
          <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Registered Tortoises</h6>
            <span class="badge badge-light"><?php echo count($tortoises); ?> records</span>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <?php if (empty($tortoises)): ?>
                <div class="text-center py-4">
                  <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                  <h5 class="text-muted">No tortoises found</h5>
                  <?php if (!empty($search)): ?>
                    <p>No results for "<?php echo htmlspecialchars($search); ?>"</p>
                    <a href="records_M.php" class="btn btn-primary">View All Tortoises</a>
                  <?php else: ?>
                    <p>No tortoises in the database yet.</p>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <table class="table table-bordered table-hover">
                  <thead class="thead-dark">
                    <tr>
                      <th scope="col">TortoiseID</th>
                      <th scope="col">Name</th>
                      <th scope="col">Age</th>
                      <th scope="col">Gender</th>
                      <th scope="col">Species</th>
                      <th scope="col">Enclosure</th>
                      <th scope="col">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($tortoises as $tortoise): ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($tortoise['ctortoiseid']); ?></strong></td>
                        <td><?php echo htmlspecialchars($tortoise['cname']); ?></td>
                        <td><?php echo htmlspecialchars($tortoise['nage']); ?> years</td>
                        <td>
                          <?php if ($tortoise['cgender']): ?>
                            <span class="badge badge-info"><?php echo htmlspecialchars($tortoise['cgender']); ?></span>
                          <?php else: ?>
                            <span class="text-muted">N/A</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if ($tortoise['species_name']): ?>
                            <div>
                              <strong><?php echo htmlspecialchars($tortoise['species_name']); ?></strong>
                              <br>
                              <small class="text-muted"><?php echo htmlspecialchars($tortoise['scientific_name']); ?></small>
                            </div>
                          <?php else: ?>
                            <span class="text-muted"><?php echo htmlspecialchars($tortoise['cspeciesid']); ?></span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if ($tortoise['cenclosureid']): ?>
                            <div>
                              <strong><?php echo htmlspecialchars($tortoise['cenclosureid']); ?></strong>
                              <?php if ($tortoise['clocation']): ?>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($tortoise['clocation']); ?></small>
                              <?php endif; ?>
                            </div>
                          <?php else: ?>
                            <span class="text-muted">N/A</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <div class="btn-group" role="group">
                            <a href="update_tortoise.php?id=<?php echo urlencode($tortoise['ctortoiseid']); ?>" 
                               class="btn btn-sm btn-info" title="Edit">
                              <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete_tortoise.php?id=<?php echo urlencode($tortoise['ctortoiseid']); ?>" 
                               class="btn btn-sm btn-danger" title="Delete"
                               onclick="return confirm('Are you sure you want to delete this tortoise?')">
                              <i class="fas fa-trash"></i>
                            </a>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div> <!-- /.container-fluid -->
    </div> <!-- /.content -->
  </div> <!-- /.content-wrapper -->
</div> <!-- /#wrapper -->

<!-- Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>

</body>
</html>