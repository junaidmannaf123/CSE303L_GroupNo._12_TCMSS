<?php
require_once 'config/database.php';

$message = '';
$error = '';

// Handle form submission for adding new tortoise
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tortoise'])) {
    try {
        // Debug: Log the POST data
        error_log("POST data received: " . print_r($_POST, true));
        
        $name = $_POST['name'] ?? '';
        $age = $_POST['age'] ?? '';
        $species = $_POST['species'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $enclosure = $_POST['enclosure'] ?? '';
        
        // Debug: Log individual values
        error_log("Name: '$name', Age: '$age', Species: '$species', Gender: '$gender', Enclosure: '$enclosure'");
        
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
        error_log("Species ID mapped to: '$species_id'");
        
        // Generate new tortoise ID
        $stmt = $pdo->query("SELECT MAX(CAST(ctortoiseid AS UNSIGNED)) as max_id FROM tbltortoise");
        $result = $stmt->fetch();
        $new_id = str_pad(($result['max_id'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
        error_log("Generated new ID: '$new_id'");
        
        // Insert new tortoise
        $query = "INSERT INTO tbltortoise (ctortoiseid, cname, nage, cgender, cenclosureid, cspeciesid) VALUES (:id, :name, :age, :gender, :enclosure, :species)";
        $stmt = $pdo->prepare($query);
        $insert_result = $stmt->execute([
            ':id' => $new_id,
            ':name' => $name,
            ':age' => $age,
            ':gender' => $gender,
            ':enclosure' => $enclosure,
            ':species' => $species_id
        ]);
        
        if ($insert_result) {
            $message = "Tortoise added successfully with ID: $new_id";
            error_log("Tortoise added successfully with ID: $new_id");
            
            // Verify the insertion
            $verify_stmt = $pdo->prepare("SELECT * FROM tbltortoise WHERE ctortoiseid = :id");
            $verify_stmt->execute([':id' => $new_id]);
            $new_tortoise = $verify_stmt->fetch();
            
            if ($new_tortoise) {
                error_log("Verification successful. New tortoise found in database.");
            } else {
                error_log("Verification failed. Tortoise not found in database after insertion.");
            }
        } else {
            throw new Exception('Failed to insert tortoise into database');
        }
        
        // Clear form data after successful submission
        $_POST = array();
        
    } catch(Exception $e) {
        $error = $e->getMessage();
        error_log("Error adding tortoise: " . $e->getMessage());
    }
}

// Handle tortoise deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    try {
        $tortoise_id = $_GET['delete'];
        
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
        
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

try {
    // Get tortoise data with species and enclosure information
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
        ORDER BY t.ctortoiseid
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tortoises = $stmt->fetchAll();
    
    // Get counts for dashboard cards
    $tortoise_count = count($tortoises);
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tblstaffmember");
    $staff_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tblenclosure WHERE cstatus = 'Active'");
    $enclosure_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tblinventory");
    $inventory_count = $stmt->fetch()['count'];
    
} catch(PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
    $tortoises = [];
    $tortoise_count = 0;
    $staff_count = 0;
    $enclosure_count = 0;
    $inventory_count = 0;
}

// Get available enclosures for the form
try {
    $enclosure_stmt = $pdo->query("SELECT cenclosureid, clocation, cenclosuretype FROM tblenclosure ORDER BY cenclosureid");
    $enclosures = $enclosure_stmt->fetchAll();
} catch(PDOException $e) {
    $enclosures = [];
}

// Get species name mapping
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
  <title>Home - Tortoise Conservation Center</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">

  <!-- Sidebar -->
  <ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
      <div class="sidebar-brand-icon rotate-n-15">
        <i class="fas fa-turtle"></i>
      </div>
      <div class="sidebar-brand-text mx-3">Admin</div>
    </a>
    <hr class="sidebar-divider my-0">
    <li class="nav-item active">
      <a class="nav-link" href="homePage.php">
        <i class="fas fa-home"></i> <span>Home</span>
      </a>
    </li>
    <li class="nav-item"><a class="nav-link" href="records_M.php"><i class="fas fa-clipboard-list"></i> Tortoise Records</a></li>
    <li class="nav-item"><a class="nav-link" href="staffMember.php"><i class="fas fa-users"></i> Staff Management</a></li>
    <li class="nav-item"><a class="nav-link" href="assignTasks.php"><i class="fas fa-tasks"></i> Assign Task</a></li>
  </ul>

  <!-- Content Wrapper -->
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">

      <!-- Topbar -->
      <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
        <h1 class="h3 text-gray-800">Tortoise Conservation Center</h1>
      </nav>

      <!-- Main Content -->
      <div class="container-fluid">

        <!-- Messages -->
        <?php if ($message): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        <?php endif; ?>

        <!-- Info Cards Row -->
        <div class="row">
          <div class="col-xl-2 col-md-3 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
              <div class="card-body d-flex align-items-center">
                <div class="mr-2">
                  <i class="fas fa-turtle fa-lg text-success"></i>
                </div>
                <div>
                  <div class="text-xs font-weight-bold text-success text-uppercase mb-0">Total Tortoises</div>
                  <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo $tortoise_count; ?></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-2 col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
              <div class="card-body d-flex align-items-center">
                <div class="mr-2">
                  <i class="fas fa-users fa-lg text-primary"></i>
                </div>
                <div>
                  <div class="text-xs font-weight-bold text-primary text-uppercase mb-0">Staff Members</div>
                  <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo $staff_count; ?></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-2 col-md-3 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
              <div class="card-body d-flex align-items-center">
                <div class="mr-2">
                  <i class="fas fa-home fa-lg text-warning"></i>
                </div>
                <div>
                  <div class="text-xs font-weight-bold text-warning text-uppercase mb-0">Active Enclosures</div>
                  <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo $enclosure_count; ?></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-2 col-md-3 mb-3">
            <div class="card border-left-dark shadow h-100 py-2">
              <div class="card-body d-flex align-items-center">
                <div class="mr-2">
                  <i class="fas fa-boxes fa-lg text-dark"></i>
                </div>
                <div>
                  <div class="text-xs font-weight-bold text-dark text-uppercase mb-0">Inventory Items</div>
                  <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo $inventory_count; ?></div>
                </div>
              </div>
            </div>
          </div>
          <!-- Weather API Section -->
          <div class="col-xl-4 col-md-12 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
              <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Current Weather</div>
                <div class="h6 mb-0 font-weight-bold text-gray-800" id="weather-location">Loading...</div>
                <div class="small text-muted" id="weather-details">Please wait...</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Add Tortoise Form -->
        <div class="card shadow mb-4">
          <div class="card-header py-3 bg-success text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-plus"></i> Add New Tortoise</h6>
          </div>
          <div class="card-body">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
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
                
                <div class="col-md-2">
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
                
                <div class="col-md-3">
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

        <!-- Admin Access Section -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
              <div class="card-header py-4 text-white border-0" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                <div class="d-flex align-items-center">
                  <i class="fas fa-crown fa-2x mr-3 text-warning"></i>
                  <div>
                    <h4 class="m-0 font-weight-bold text-white">Admin Access - User Dashboards</h4>
                    <small class="text-light">Full system access and control</small>
                  </div>
                </div>
              </div>
              <div class="card-body p-4" style="background: rgba(255,255,255,0.95);">
                <div class="row justify-content-center">
                  <div class="col-lg-4 col-md-6 mb-3">
                    <div class="dashboard-card" data-href="vetdashboard.php">
                      <div class="card-icon-wrapper bg-primary">
                        <i class="fas fa-user-md"></i>
                      </div>
                      <h5 class="card-title">Veterinarian Dashboard</h5>
                      <p class="card-description">Access vet tools and health records</p>
                      <div class="card-overlay">
                        <i class="fas fa-arrow-right"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-4 col-md-6 mb-3">
                    <div class="dashboard-card" data-href="caretaker_dashboard.php">
                      <div class="card-icon-wrapper bg-success">
                        <i class="fas fa-user-nurse"></i>
                      </div>
                      <h5 class="card-title">Caretaker Dashboard</h5>
                      <p class="card-description">Access feeding and care logs</p>
                      <div class="card-overlay">
                        <i class="fas fa-arrow-right"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-4 col-md-6 mb-3">
                    <div class="dashboard-card" data-href="inventory_dashboard.php">
                      <div class="card-icon-wrapper bg-warning">
                        <i class="fas fa-boxes"></i>
                      </div>
                      <h5 class="card-title">Inventory Dashboard</h5>
                      <p class="card-description">Manage inventory and supplies</p>
                      <div class="card-overlay">
                        <i class="fas fa-arrow-right"></i>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row justify-content-center">
                  <div class="col-lg-4 col-md-6 mb-3">
                    <div class="dashboard-card" data-href="BSDashboard.php">
                      <div class="card-icon-wrapper bg-info">
                        <i class="fas fa-dna"></i>
                      </div>
                      <h5 class="card-title">Breeding Specialist</h5>
                      <p class="card-description">Access breeding and mating data</p>
                      <div class="card-overlay">
                        <i class="fas fa-arrow-right"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-4 col-md-6 mb-3">
                    <div class="dashboard-card" data-href="Environment_Monitor.php">
                      <div class="card-icon-wrapper bg-secondary">
                        <i class="fas fa-leaf"></i>
                      </div>
                      <h5 class="card-title">Environment Monitor</h5>
                      <p class="card-description">Monitor environmental conditions</p>
                      <div class="card-overlay">
                        <i class="fas fa-arrow-right"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-4 col-md-6 mb-3">
                    <div class="dashboard-card" data-href="enclosure_dashboard.html">
                      <div class="card-icon-wrapper bg-success">
                        <i class="fas fa-home"></i>
                      </div>
                      <h5 class="card-title">Enclosure</h5>
                      <p class="card-description">Manage tortoise enclosures</p>
                      <div class="card-overlay">
                        <i class="fas fa-arrow-right"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <style>
        .dashboard-card {
          position: relative;
          background: white;
          border-radius: 15px;
          padding: 1.5rem 1rem;
          text-align: center;
          box-shadow: 0 10px 30px rgba(0,0,0,0.1);
          transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
          cursor: pointer;
          overflow: hidden;
          border: 2px solid transparent;
          height: 200px;
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
        }

        .dashboard-card:hover {
          transform: translateY(-10px) scale(1.02);
          box-shadow: 0 20px 40px rgba(0,0,0,0.15);
          border-color: #667eea;
        }

        .dashboard-card::before {
          content: '';
          position: absolute;
          top: 0;
          left: -100%;
          width: 100%;
          height: 100%;
          background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
          transition: left 0.5s;
        }

        .dashboard-card:hover::before {
          left: 100%;
        }

        .card-icon-wrapper {
          width: 60px;
          height: 60px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          margin: 0 auto 1rem;
          transition: all 0.3s ease;
          position: relative;
          z-index: 2;
          flex-shrink: 0;
        }

        .card-icon-wrapper i {
          font-size: 1.5rem;
          color: white;
        }

        .dashboard-card:hover .card-icon-wrapper {
          transform: scale(1.1) rotate(5deg);
        }

        .card-title {
          font-weight: 700;
          color: #2d3748;
          margin-bottom: 0.3rem;
          font-size: 1rem;
          transition: color 0.3s ease;
          flex-shrink: 0;
        }

        .card-description {
          color: #718096;
          font-size: 0.8rem;
          margin-bottom: 0;
          line-height: 1.3;
          flex-shrink: 0;
        }

        .card-overlay {
          position: absolute;
          top: 50%;
          right: -50px;
          transform: translateY(-50%);
          width: 40px;
          height: 40px;
          background: #667eea;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          opacity: 0;
          transition: all 0.3s ease;
        }

        .card-overlay i {
          color: white;
          font-size: 1rem;
        }

        .dashboard-card:hover .card-overlay {
          opacity: 1;
          right: 20px;
        }

        .dashboard-card:hover .card-title {
          color: #667eea;
        }

        @keyframes fadeInUp {
          from {
            opacity: 0;
            transform: translateY(30px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }

        .dashboard-card {
          animation: fadeInUp 0.6s ease forwards;
        }

        .dashboard-card:nth-child(1) { animation-delay: 0.1s; }
        .dashboard-card:nth-child(2) { animation-delay: 0.2s; }
        .dashboard-card:nth-child(3) { animation-delay: 0.3s; }
        .dashboard-card:nth-child(4) { animation-delay: 0.4s; }
        .dashboard-card:nth-child(5) { animation-delay: 0.5s; }
        .dashboard-card:nth-child(6) { animation-delay: 0.6s; }

        /* Compact chart styling */
        .chart-area {
          height: 300px;
          position: relative;
        }

        .chart-pie {
          height: 300px;
          position: relative;
        }

        .chart-bar {
          height: 300px;
          position: relative;
        }

        .chart-line {
          height: 300px;
          position: relative;
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
          const cards = document.querySelectorAll('.dashboard-card');
          
          cards.forEach(card => {
            card.addEventListener('click', function() {
              const href = this.getAttribute('data-href');
              if (href) {
                window.location.href = href;
              }
            });
          });
        });
        </script>

        <!-- Interactive Charts Section -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="card shadow-lg border-0">
              <div class="card-header py-3 bg-gradient-primary text-white">
                <h6 class="m-0 font-weight-bold">Tortoise Population Trends</h6>
              </div>
              <div class="card-body">
                <div class="chart-area">
                  <canvas id="tortoiseTrendChart"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tortoise Status Table -->
        <div class="card shadow mb-4">
          <div class="card-header py-3 bg-gradient-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Current Tortoise Status</h6>
            <span class="badge badge-light"><?php echo count($tortoises); ?> records</span>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <?php if (empty($tortoises)): ?>
                <div class="text-center py-4">
                  <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                  <h5 class="text-muted">No tortoises found</h5>
                  <p>No tortoises in the database yet.</p>
                </div>
              <?php else: ?>
                <table class="table table-bordered table-hover">
                  <thead class="thead-light">
                    <tr>
                      <th>TortoiseID</th>
                      <th>Name</th>
                      <th>Age</th>
                      <th>Gender</th>
                      <th>Species</th>
                      <th>Enclosure</th>
                      <th>Actions</th>
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
                            <a href="?delete=<?php echo urlencode($tortoise['ctortoiseid']); ?>" 
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
  </div> <!-- /#content-wrapper -->
</div> <!-- /#wrapper -->

<!-- Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>
<script src="vendor/chart.js/Chart.min.js"></script>

<!-- Weather Script -->
<script>
    fetch('http://api.weatherapi.com/v1/current.json?key=494aa14050f64e0baed235450252107&q=Dhaka&aqi=no')
    .then(response => response.json())
    .then(data => {
      document.getElementById('weather-location').textContent = data.location.name + ', ' + data.location.country;
      document.getElementById('weather-details').textContent = `Temperature: ${data.current.temp_c}°C, Condition: ${data.current.condition.text}, Humidity: ${data.current.humidity}%`;
    })
    .catch(error => {
      console.error('Error fetching weather data:', error);
      document.getElementById('weather-location').textContent = 'Weather data unavailable';
      document.getElementById('weather-details').textContent = '';
    });
   
</script>

<!-- Interactive Charts Script -->
<script>
// Tortoise Population Trends Chart
const tortoiseTrendCtx = document.getElementById('tortoiseTrendChart').getContext('2d');
const tortoiseTrendChart = new Chart(tortoiseTrendCtx, {
  type: 'line',
  data: {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [{
      label: 'Total Tortoises',
      data: [3, 4, 4, 5, 5, 6, 6, 7, 7, 8, 8, 9],
      borderColor: '#4e73df',
      backgroundColor: 'rgba(78, 115, 223, 0.1)',
      borderWidth: 3,
      fill: true,
      tension: 0.4
    }, {
      label: 'New Arrivals',
      data: [1, 0, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1],
      borderColor: '#1cc88a',
      backgroundColor: 'rgba(28, 200, 138, 0.1)',
      borderWidth: 2,
      fill: false,
      tension: 0.4
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top',
      },
      tooltip: {
        mode: 'index',
        intersect: false,
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: {
          color: 'rgba(0,0,0,0.1)'
        }
      },
      x: {
        grid: {
          display: false
        }
      }
    },
    interaction: {
      mode: 'nearest',
      axis: 'x',
      intersect: false
    }
  }
});

// Add hover effects to charts
document.querySelectorAll('canvas').forEach(canvas => {
  canvas.style.cursor = 'pointer';
  canvas.addEventListener('click', function() {
    // Add click functionality for chart interactions
    console.log('Chart clicked:', this.id);
  });
});
</script> 

</body>
</html> 
