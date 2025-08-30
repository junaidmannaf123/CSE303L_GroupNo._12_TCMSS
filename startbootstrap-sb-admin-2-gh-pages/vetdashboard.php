<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['staff_id']) || !in_array($_SESSION['role'], ['Veterinarian', 'Manager'])) {
    // Clear any invalid session
    session_destroy();
    header('Location: login.php');
    exit();
}

// Check if session is still valid (optional: add timeout check)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 3600) { // 1 hour timeout
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}

// Fetch tortoises with species and enclosure info
$tortoises = [];
try {
    $stmt = $pdo->prepare(
        "SELECT 
            t.ctortoiseid,
            t.cname,
            t.nage,
            t.cgender,
            t.cenclosureid,
            t.cspeciesid,
            s.ccommonname AS species_name,
            s.cscientificname AS scientific_name,
            e.cenclosuretype,
            e.clocation,
            e.csize
        FROM tbltortoise t
        LEFT JOIN tblspecies s ON t.cspeciesid = s.cspeciesid
        LEFT JOIN tblenclosure e ON t.cenclosureid = e.cenclosureid
        ORDER BY t.ctortoiseid"
    );
    $stmt->execute();
    $tortoises = $stmt->fetchAll();
} catch (Throwable $e) {
    $tortoises = [];
}

// Fetch medical records
$medical = [];
try {
    $stmt = $pdo->prepare(
        "SELECT 
            crecordid,
            drecordingdate,
            cdiagnosis,
            ctreatment,
            ctype,
            ddate,
            cvaccinationstatus,
            dcheckdate,
            dchecktime,
            cstaffid,
            ctortoiseid
        FROM tblmedicalrecords"
    );
    $stmt->execute();
    $medical = $stmt->fetchAll();
} catch (Throwable $e) {
    $medical = [];
}

$totalTortoises = count($tortoises);
$medicalCount = count($medical);

// Aggregate helpers
function normalizeDateForSeries($r) {
    $candidates = [
        isset($r['dcheckdate']) ? $r['dcheckdate'] : '',
        isset($r['ddate']) ? $r['ddate'] : '',
        isset($r['drecordingdate']) ? $r['drecordingdate'] : ''
    ];
    foreach ($candidates as $d) {
        if (!$d) continue;
        $ts = strtotime($d);
        if ($ts) return date('Y-m', $ts);
    }
    return '';
}

// Monthly series
$byMonth = [];
foreach ($medical as $r) {
    $label = normalizeDateForSeries($r);
    if ($label === '') continue;
    $byMonth[$label] = isset($byMonth[$label]) ? ($byMonth[$label] + 1) : 1;
}
$months = array_keys($byMonth);
sort($months);
$monthSeries = [];
foreach ($months as $m) { $monthSeries[] = ['label' => $m, 'count' => $byMonth[$m]]; }
if (count($monthSeries) === 0) {
    $today = new DateTime();
    for ($i = 5; $i >= 0; $i--) {
        $d = (clone $today)->modify("-{$i} months");
        $label = $d->format('Y-m');
        $monthSeries[] = ['label' => $label, 'count' => 0];
    }
}

// Type counts
$typeCounts = [];
foreach ($medical as $r) {
    $t = isset($r['ctype']) ? trim((string)$r['ctype']) : 'Unknown';
    if ($t === '') $t = 'Unknown';
    $typeCounts[$t] = isset($typeCounts[$t]) ? ($typeCounts[$t] + 1) : 1;
}

// Vaccination counts
$vaccCounts = ['upToDate' => 0, 'due' => 0, 'overdue' => 0];
foreach ($medical as $r) {
    $s = strtolower(trim((string)($r['cvaccinationstatus'] ?? '')));
    if ($s === '') continue;
    if (strpos($s, 'overdue') !== false) $vaccCounts['overdue']++;
    else if (strpos($s, 'due') !== false || strpos($s, 'pending') !== false) $vaccCounts['due']++;
    else $vaccCounts['upToDate']++;
}

// Species distribution
$speciesCounts = [];
foreach ($tortoises as $t) {
    $s = isset($t['species_name']) && $t['species_name'] !== '' ? $t['species_name'] : (isset($t['cspeciesid']) ? $t['cspeciesid'] : 'Unknown');
    $speciesCounts[$s] = isset($speciesCounts[$s]) ? ($speciesCounts[$s] + 1) : 1;
}

// Gender counts
$genderCounts = [];
foreach ($tortoises as $t) {
    $g = strtolower(trim((string)($t['cgender'] ?? 'unknown')));
    $norm = ($g === 'm') ? 'male' : (($g === 'f') ? 'female' : $g);
    $label = ucfirst($norm);
    $genderCounts[$label] = isset($genderCounts[$label]) ? ($genderCounts[$label] + 1) : 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Tortoise Conservation Management System Dashboard">
    <meta name="author" content="">
    <title>Tortoise Conservation - Veterinarian Dashboard</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .clickable-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .clickable-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
        }
        
        .clickable-card:hover .text-gray-300 {
            color: #5a5c69 !important;
        }
        
        a.text-decoration-none:hover {
            text-decoration: none !important;
        }
        
        /* Ensure text colors remain consistent on hover */
        .clickable-card:hover .text-success,
        .clickable-card:hover .text-info,
        .clickable-card:hover .text-warning,
        .clickable-card:hover .text-danger,
        .clickable-card:hover .text-primary,
        .clickable-card:hover .text-secondary {
            color: inherit !important;
        }

        /* Consistent topbar height */
        .topbar-fixed { min-height: 4.75rem; }

        /* Ensure equal height for summary cards */
        .dashboard-equal-row > [class^="col-"] { display: flex; }
        .dashboard-equal-row .card { height: 100%; width: 100%; }
        
        /* Ensure equal height for statistic cards */
        .row > [class*="col-"] > .card { height: 100%; }
        .row > [class*="col-"] > a > .card { height: 100%; }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="vetdashboard.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-turtle"></i>
                </div>
                <div class="sidebar-brand-text mx-3">TCMSS</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item active">
                <a class="nav-link" href="vetdashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Vet Tools</div>
            <li class="nav-item">
                <a class="nav-link" href="health-records.php">
                    <i class="fas fa-fw fa-notes-medical"></i>
                    <span>Health Records</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="tortoise-list.php">
                    <i class="fas fa-fw fa-list"></i>
                    <span>View Tortoise List</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="assigned_tasks.php">
                    <i class="fas fa-fw fa-tasks"></i>
                    <span>Assigned Tasks</span></a>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow topbar-fixed">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <h4 class="ml-3 mt-2 text-success font-weight-bold mr-auto">Veterinarian Dashboard</h4>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                  <?php echo htmlspecialchars($_SESSION['staff_name']); ?> 
                                  <span class="badge badge-success"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                                </span>
                                <i class="fas fa-user-nurse fa-2x text-success img-profile rounded-circle"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Profile</a>
                                <a class="dropdown-item" href="#"><i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Settings</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Logout</a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Welcome, <?php echo htmlspecialchars($_SESSION['staff_name']); ?>!</h1>
                    </div>
                    <!-- Statistic Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-lg-3 col-md-6 mb-4">
                            <a href="tortoise-list.php" class="text-decoration-none">
                                <div class="card border-left-success shadow h-100 py-2 clickable-card">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Tortoises</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalTortoises; ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-turtle fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 mb-4">
                            <a href="assigned_tasks.php" class="text-decoration-none">
                                <div class="card border-left-primary shadow h-100 py-2 clickable-card">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tasks Complete</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">15</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 mb-4">
                            <a href="assigned_tasks.php" class="text-decoration-none">
                                <div class="card border-left-secondary shadow h-100 py-2 clickable-card">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Tasks Incomplete</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">7</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xl-2.5 col-lg-3 col-md-6 mb-4">
                            <a href="health-records.php" class="text-decoration-none"> 
                            <div class="card border-left-success shadow h-100 py-2 clickable-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Health Records</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><span id="hr-count-stat"><?php echo $medicalCount; ?></span></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-notes-medical fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Charts Row -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-success">Health Checks Over Time (by Check Date)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="myAreaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Tortoise Health Status + Vaccination Status Summary -->
                    <div class="row dashboard-equal-row">
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-success">Tortoise Health Status by Type</h6>
                                </div>
                                <div class="card-body">
                                    <div class="pt-2 pb-2">
                                        <canvas id="typeBarChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-success">Vaccination Status Summary</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="vaccinationPieChart"></canvas>
                                    </div>
                                    <div class="mt-3 d-flex justify-content-around text-center small">
                                        <div>
                                            <div class="h4 text-success mb-0" id="vacc-up-to-date">0</div>
                                            <div class="text-muted">Up to Date</div>
                                        </div>
                                        <div>
                                            <div class="h4 text-warning mb-0" id="vacc-due">0</div>
                                            <div class="text-muted">Due</div>
                                        </div>
                                        <div>
                                            <div class="h4 text-danger mb-0" id="vacc-overdue">0</div>
                                            <div class="text-muted">Overdue</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Health Records, Tortoise Records, Alerts, and Tasks -->
                    <h3 class="h5 text-gray-800 mt-4 mb-3">Records Overview</h3>
                    <div class="row" id="health-records-section">
                        <div class="col-md-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success">Tortoises Gender Summary</h6>
                                </div>
                                <div class="card-body" style="min-height: 420px;">
                                    <div class="pt-2 pb-2">
                                        <canvas id="genderPieChart" height="280"></canvas>
                                    </div>
                                    <div class="text-center mt-2">
                                        <p class="text-muted small">Based on latest tortoise registry data</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4" id="tortoise-records-section">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success">Species Distribution</h6>
                                </div>
                                <div class="card-body" style="min-height: 520px;">
                                    <div class="pt-2 pb-2">
                                        <canvas id="speciesPieChart" height="340"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Tortoise Conservation 2025</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <!-- Add Health Record Modal -->
    <div class="modal fade" id="addHealthRecordModal" tabindex="-1" role="dialog" aria-labelledby="addHealthRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success" id="addHealthRecordModalLabel">
                        <i class="fas fa-plus-circle mr-2"></i>Add New Health Record
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addHealthRecordForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tortoiseId" class="font-weight-bold text-gray-800">Tortoise ID *</label>
                                    <input type="text" class="form-control" id="tortoiseId" name="tortoiseId" required placeholder="Enter tortoise ID">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="recordDate" class="font-weight-bold text-gray-800">Record Date *</label>
                                    <input type="date" class="form-control" id="recordDate" name="recordDate" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="healthStatus" class="font-weight-bold text-gray-800">Health Status *</label>
                                    <select class="form-control" id="healthStatus" name="healthStatus" required>
                                        <option value="">Select status</option>
                                        <option value="healthy">Healthy</option>
                                        <option value="sick">Sick</option>
                                        <option value="recovering">Recovering</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="weight" class="font-weight-bold text-gray-800">Weight (kg)</label>
                                    <input type="number" class="form-control" id="weight" name="weight" step="0.1" placeholder="Enter weight">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="temperature" class="font-weight-bold text-gray-800">Temperature (°C)</label>
                                    <input type="number" class="form-control" id="temperature" name="temperature" step="0.1" placeholder="Enter temperature">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="heartRate" class="font-weight-bold text-gray-800">Heart Rate (bpm)</label>
                                    <input type="number" class="form-control" id="heartRate" name="heartRate" placeholder="Enter heart rate">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="symptoms" class="font-weight-bold text-gray-800">Symptoms/Observations</label>
                            <textarea class="form-control" id="symptoms" name="symptoms" rows="3" placeholder="Describe any symptoms or observations"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="treatment" class="font-weight-bold text-gray-800">Treatment/Medication</label>
                            <textarea class="form-control" id="treatment" name="treatment" rows="3" placeholder="Describe any treatment or medication administered"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="notes" class="font-weight-bold text-gray-800">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes or comments"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="veterinarian" class="font-weight-bold text-gray-800">Veterinarian</label>
                                    <input type="text" class="form-control" id="veterinarian" name="veterinarian" value="Farhana Rahman" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nextCheckup" class="font-weight-bold text-gray-800">Next Checkup Date</label>
                                    <input type="date" class="form-control" id="nextCheckup" name="nextCheckup">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="button" onclick="saveHealthRecord()">
                        <i class="fas fa-save mr-1"></i>Save Record
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Add Health Record Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-success" href="login.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>
    
    <!-- Health Record Modal JavaScript -->
    <script>
        // Live dashboard data using embedded PHP (no API fetch)
        document.addEventListener('DOMContentLoaded', function () {
            let vaccinationChart = null;
            let typeBarChart = null;
            let checksLineChart = null;
            let speciesPieChart = null;
            let genderPieChart = null;

            function setCardNumberByLabel(labelText, value) {
                const cards = document.querySelectorAll('.card');
                for (const card of cards) {
                    const label = card.querySelector('.text-xs');
                    const num = card.querySelector('.h5');
                    if (label && num && label.textContent.trim().toLowerCase() === labelText.trim().toLowerCase()) {
                        num.textContent = String(value);
                        return;
                    }
                }
            }

            function renderVaccinationSummary(upToDate, due, overdue) {
                const upEl = document.getElementById('vacc-up-to-date');
                const dueEl = document.getElementById('vacc-due');
                const overEl = document.getElementById('vacc-overdue');
                if (upEl) upEl.textContent = upToDate;
                if (dueEl) dueEl.textContent = due;
                if (overEl) overEl.textContent = overdue;

                const ctx = document.getElementById('vaccinationPieChart');
                if (ctx && window.Chart) {
                    // Destroy existing chart if it exists
                    if (vaccinationChart) {
                        vaccinationChart.destroy();
                        vaccinationChart = null;
                    }
                    
                    // Create new chart instance
                    vaccinationChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Up to Date', 'Due', 'Overdue'],
                            datasets: [{
                                data: [upToDate, due, overdue],
                                backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                                hoverBackgroundColor: ['#17a673', '#dda20a', '#be2617'],
                                hoverBorderColor: 'rgba(234, 236, 244, 1)'
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            tooltips: {
                                backgroundColor: 'rgb(255,255,255)',
                                bodyFontColor: '#858796',
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                caretPadding: 10
                            },
                            legend: { display: false },
                            cutoutPercentage: 70
                        }
                    });
                }
            }

            function renderTypeBarChart(typeCounts) {
                const ctx = document.getElementById('typeBarChart');
                if (!ctx || !window.Chart) return;
                
                // Destroy existing chart if it exists
                if (typeBarChart) {
                    typeBarChart.destroy();
                    typeBarChart = null;
                }
                
                const labels = Object.keys(typeCounts);
                const values = Object.values(typeCounts);
                
                // Create new chart instance
                typeBarChart = new Chart(ctx, {
                    type: 'bar',
                    data: { 
                        labels, 
                        datasets: [{ 
                            label: 'Records by Type', 
                            data: values, 
                            backgroundColor: '#36a2eb' 
                        }] 
                    },
                    options: { 
                        legend: { display: false }, 
                        maintainAspectRatio: false, 
                        responsive: true,
                        scales: { 
                            yAxes: [{ 
                                ticks: { beginAtZero: true, precision: 0 } 
                            }] 
                        } 
                    }
                });
            }

            function renderChecksLineChart(series) {
                const ctx = document.getElementById('myAreaChart');
                if (!ctx || !window.Chart) return;
                
                // Destroy existing chart if it exists
                if (checksLineChart) {
                    checksLineChart.destroy();
                    checksLineChart = null;
                }
                
                const labels = series.map(p => p.label);
                const values = series.map(p => p.count);
                
                // Create new chart instance
                checksLineChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Health Checks',
                            data: values,
                            lineTension: 0.3,
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            pointRadius: 3,
                            pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                            pointBorderColor: 'rgba(78, 115, 223, 1)',
                            pointHoverRadius: 3,
                            pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                            pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                            pointHitRadius: 10,
                            pointBorderWidth: 2
                        }]
                    },
                    options: { 
                        maintainAspectRatio: false, 
                        legend: { display: false },
                        responsive: true,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            tooltip: {
                                enabled: true,
                                mode: 'index',
                                intersect: false
                            }
                        }
                    }
                });
            }

            function renderGenderPie(counts) {
                const ctx = document.getElementById('genderPieChart');
                if (!ctx || !window.Chart) return;
                
                // Destroy existing chart if it exists
                if (genderPieChart) {
                    genderPieChart.destroy();
                    genderPieChart = null;
                }
                
                const labels = Object.keys(counts);
                const values = Object.values(counts);
                const colors = ['#36b9cc','#f6c23e','#e74a3b','#1cc88a'];
                
                // Create new chart instance
                genderPieChart = new Chart(ctx, {
                    type: 'pie',
                    data: { 
                        labels, 
                        datasets: [{ 
                            data: values, 
                            backgroundColor: colors.slice(0, labels.length) 
                        }] 
                    },
                    options: { 
                        maintainAspectRatio: false, 
                        responsive: true,
                        legend: { display: true } 
                    }
                });
            }

            const fmtMonth = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;

            // Embedded data from PHP
            const tortoises = <?php echo json_encode($tortoises); ?>;
            const medical = <?php echo json_encode($medical); ?>;
            const monthSeries = <?php echo json_encode($monthSeries); ?>;
            const typeCounts = <?php echo json_encode($typeCounts); ?>;
            const vaccCounts = <?php echo json_encode($vaccCounts); ?>;
            const speciesCounts = <?php echo json_encode($speciesCounts); ?>;
            const genderCounts = <?php echo json_encode($genderCounts); ?>;

            // Counters
            setCardNumberByLabel('Total Tortoises', tortoises.length);
            const hrCountStatEl = document.getElementById('hr-count-stat');
            if (hrCountStatEl) hrCountStatEl.textContent = String(medical.length);

            // Charts and summaries
            renderChecksLineChart(monthSeries);
            renderTypeBarChart(typeCounts);
            ['Illness','Injury','Checkup','Emergency','Monitoring','Infection','Surgery','Recovery','Nutrition'].forEach(tp => {
                const el = document.getElementById('type-count-' + tp);
                if (el) el.textContent = String(typeCounts[tp] || 0);
            });
            renderVaccinationSummary(vaccCounts.upToDate, vaccCounts.due, vaccCounts.overdue);

            function setTile(labelContains, value) {
                const cards = document.querySelectorAll('#health-records-section .card .card-body .row .col-md-6');
                for (const col of cards) {
                    const textMuted = col.querySelector('.text-muted');
                    const num = col.querySelector('.h2');
                    if (textMuted && num && textMuted.textContent.toLowerCase().includes(labelContains)) {
                        num.textContent = String(value);
                        const badge = col.querySelector('.badge');
                        if (badge) badge.textContent = Math.round((value / Math.max(1, tortoises.length)) * 100) + '%';
                    }
                }
            }
            const toType = v => String(v || '').trim();
            setTile('healthy', medical.filter(r => ['Checkup','Recovery','Nutrition'].includes(toType(r.ctype))).length);
            setTile('recovering', medical.filter(r => toType(r.ctype) === 'Recovery').length);
            setTile('sick', medical.filter(r => ['Illness','Infection','Injury'].includes(toType(r.ctype))).length);
            setTile('critical', medical.filter(r => toType(r.ctype) === 'Emergency').length);

            // Species distribution chart
            (function(){
                const spLabels = Object.keys(speciesCounts);
                const spValues = Object.values(speciesCounts);
                const spCtx = document.getElementById('speciesPieChart');
                if (spCtx && window.Chart) {
                    if (speciesPieChart) { speciesPieChart.destroy(); speciesPieChart = null; }
                    speciesPieChart = new Chart(spCtx, {
                        type: 'pie',
                        data: { labels: spLabels, datasets: [{ data: spValues, backgroundColor: ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#fd7e14','#20c997','#6f42c1','#17a2b8'] }] },
                        options: { maintainAspectRatio: false, responsive: true, legend: { display: true } }
                    });
                }
            })();

            // Gender pie chart
            renderGenderPie(genderCounts);
        });

        // Set default date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('recordDate').value = today;
        });

        function saveHealthRecord() {
            // Get form data
            const form = document.getElementById('addHealthRecordForm');
            const formData = new FormData(form);
            
            // Validate required fields
            const tortoiseId = formData.get('tortoiseId');
            const recordDate = formData.get('recordDate');
            const healthStatus = formData.get('healthStatus');
            
            if (!tortoiseId || !recordDate || !healthStatus) {
                alert('Please fill in all required fields (marked with *)');
                return;
            }
            
            // Create health record object
            const healthRecord = {
                tortoiseId: tortoiseId,
                recordDate: recordDate,
                healthStatus: healthStatus,
                weight: formData.get('weight'),
                temperature: formData.get('temperature'),
                heartRate: formData.get('heartRate'),
                symptoms: formData.get('symptoms'),
                treatment: formData.get('treatment'),
                notes: formData.get('notes'),
                veterinarian: formData.get('veterinarian'),
                nextCheckup: formData.get('nextCheckup')
            };
            
            // Here you would typically send the data to your backend
            // For now, we'll just show a success message
            console.log('Health Record Data:', healthRecord);
            
            // Show success message
            alert('Health record saved successfully!');
            
            // Reset form
            form.reset();
            document.getElementById('recordDate').value = new Date().toISOString().split('T')[0];
            document.getElementById('veterinarian').value = 'Farhana Rahman';
            
            // Close modal
            $('#addHealthRecordModal').modal('hide');
            
            // Optionally refresh the page or update the dashboard
            // location.reload();
        }
        
        // Clear form when modal is closed
        $('#addHealthRecordModal').on('hidden.bs.modal', function () {
            document.getElementById('addHealthRecordForm').reset();
            document.getElementById('recordDate').value = new Date().toISOString().split('T')[0];
            document.getElementById('veterinarian').value = 'Farhana Rahman';
        });
    </script>
</body>
</html>
