<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['staff_id']) || !in_array($_SESSION['role'], ['Breeding Specialist', 'Manager'])) {
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

// Fetch data directly in PHP
try {
    // Get total eggs from breeding records
    $stmt = $mysqli->prepare("SELECT SUM(neggscount) as total_eggs FROM tblbreedingrecord WHERE neggscount IS NOT NULL AND neggscount > 0");
    $stmt->execute();
    $result = $stmt->get_result();
    $total_eggs = $result->fetch_assoc()['total_eggs'] ?? 0;

    // Get average hatching service rate from breeding records
    $stmt = $mysqli->prepare("SELECT AVG(nhatchingservicerate) as avg_hatch_rate FROM tblbreedingrecord WHERE nhatchingservicerate IS NOT NULL AND nhatchingservicerate > 0");
    $stmt->execute();
    $result = $stmt->get_result();
    $avg_hatch_rate = $result->fetch_assoc()['avg_hatch_rate'] ?? 0;

    // Get egg condition vs average weight data
    $stmt = $mysqli->prepare("
        SELECT 
            ceggcondition,
            AVG(nweight) as avg_weight,
            COUNT(*) as count
        FROM tbleggdetails 
        WHERE ceggcondition IS NOT NULL AND ceggcondition != '' AND nweight IS NOT NULL AND nweight > 0
        GROUP BY ceggcondition 
        ORDER BY avg_weight DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $condition_weights = [];
    while ($row = $result->fetch_assoc()) {
        $condition_weights[] = [
            'condition' => $row['ceggcondition'],
            'avgWeight' => round($row['avg_weight'], 1),
            'count' => $row['count']
        ];
    }

    // Get hatching service rate distribution for pie chart
    $stmt = $mysqli->prepare("
        SELECT 
            CASE 
                WHEN nhatchingservicerate >= 80 THEN 'Excellent (80-100%)'
                WHEN nhatchingservicerate >= 70 THEN 'Good (70-79%)'
                WHEN nhatchingservicerate >= 60 THEN 'Fair (60-69%)'
                WHEN nhatchingservicerate >= 50 THEN 'Poor (50-59%)'
                ELSE 'Very Poor (<50%)'
            END as rate_category,
            COUNT(*) as count
        FROM tblbreedingrecord 
        WHERE nhatchingservicerate IS NOT NULL AND nhatchingservicerate > 0
        GROUP BY rate_category
        ORDER BY 
            CASE rate_category
                WHEN 'Excellent (80-100%)' THEN 1
                WHEN 'Good (70-79%)' THEN 2
                WHEN 'Fair (60-69%)' THEN 3
                WHEN 'Poor (50-59%)' THEN 4
                WHEN 'Very Poor (<50%)' THEN 5
            END
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $hatch_rate_distribution = [];
    while ($row = $result->fetch_assoc()) {
        $hatch_rate_distribution[] = [
            'category' => $row['rate_category'],
            'count' => $row['count']
        ];
    }

} catch (Exception $e) {
    $error_message = 'Database error: ' . $e->getMessage();
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
    <title>Tortoise Conservation - Breeding Specialist Dashboard</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-turtle"></i>
                </div>
                <div class="sidebar-brand-text mx-3"><strong style="font-size: 1.8em;">TCMSS</strong></div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item active">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Breeding Tools</div>
            <li class="nav-item">
                <a class="nav-link" href="mating_pair.php">
                    <i class="fas fa-heart"></i>
                    <span>Mating Pairs</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="egg_data.php">
                    <i class="fas fa-egg"></i>
                    <span>Egg Data</span></a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="assigned_tasks_BS.php">
                    <i class="fas fa-tasks"></i>
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
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <h4 class="ml-3 mt-2 text-success font-weight-bold d-inline-block">Breeding Specialist Dashboard</h4>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?php echo htmlspecialchars($_SESSION['staff_name']); ?> 
                                    <span class="badge badge-success"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                                </span>
                                <i class="fas fa-user fa-2x text-success img-profile rounded-circle"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelle/config/database.phpy="userDropdown">
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
                    <!-- Header -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Welcome, <?php echo htmlspecialchars($_SESSION['staff_name']); ?>!</h1>
                    </div>

                    <!-- Error Display -->
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Top Row: Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Eggs (from Mating Pairs)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_eggs; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-egg fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Average Hatching Rate</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo round($avg_hatch_rate, 1); ?>%</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Breeding Records</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($hatch_rate_distribution) > 0 ? array_sum(array_column($hatch_rate_distribution, 'count')) : 0; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-heart fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Egg Conditions</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($condition_weights); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row: Side by Side -->
                    <div class="row">
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-pie"></i> Hatching Service Rate Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="hatchRateDistributionPie"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-success"></i> Excellent (80-100%)
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-info"></i> Good (70-79%)
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-warning"></i> Fair (60-69%)
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-danger"></i> Poor (50-59%)
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-secondary"></i> Very Poor (<50%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-chart-bar"></i> Egg Condition vs Average Weight</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-bar">
                                        <canvas id="conditionWeightBar"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Tables Row -->
                    <div class="row">
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table"></i> Hatching Rate Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Rate Category</th>
                                                    <th>Count</th>
                                                    <th>Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $total_records = array_sum(array_column($hatch_rate_distribution, 'count'));
                                                foreach ($hatch_rate_distribution as $rate): 
                                                    $percentage = $total_records > 0 ? round(($rate['count'] / $total_records) * 100, 1) : 0;
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($rate['category']); ?></td>
                                                    <td><?php echo $rate['count']; ?></td>
                                                    <td><?php echo $percentage; ?>%</td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-table"></i> Egg Condition vs Weight</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Condition</th>
                                                    <th>Average Weight (g)</th>
                                                    <th>Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($condition_weights as $condition): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($condition['condition']); ?></td>
                                                    <td><?php echo $condition['avgWeight']; ?>g</td>
                                                    <td><?php echo $condition['count']; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Record Modal -->
    <div class="modal fade" id="addRecordModal" tabindex="-1" role="dialog" aria-labelle/config/database.phpy="addRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRecordModalLabel"><i class="fas fa-plus"></i> Add New Record</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addRecordForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="recordType">Record Type</label>
                                    <select class="form-control" id="recordType" required>
                                        <option value="">Select Record Type</option>
                                        <option value="mating_pair">Mating Pair</option>
                                        <option value="egg_data">Egg Data</option>
                                        
                                        <option value="task">Task</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="recordDate">Record Date</label>
                                    <input type="date" class="form-control" id="recordDate" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mating Pair Fields -->
                        <div id="matingPairFields" style="display: none;">
                            <hr>
                            <h6 class="text-primary">Mating Pair Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="maleTortoise">Male Tortoise ID</label>
                                        <input type="text" class="form-control" id="maleTortoise" placeholder="e.g., T-001">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="femaleTortoise">Female Tortoise ID</label>
                                        <input type="text" class="form-control" id="femaleTortoise" placeholder="e.g., T-002">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pairStatus">Pair Status</label>
                                <select class="form-control" id="pairStatus">
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="unsuccessful">Unsuccessful</option>
                                </select>
                            </div>
                        </div>

                        <!-- Egg Data Fields -->
                        <div id="eggDataFields" style="display: none;">
                            <hr>
                            <h6 class="text-success">Egg Data Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="batchID">Batch ID</label>
                                        <input type="text" class="form-control" id="batchID" placeholder="e.g., EB-010">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="totalEggs">Total Eggs</label>
                                        <input type="number" class="form-control" id="totalEggs" min="1" placeholder="Number of eggs">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fertilityRate">Fertility Rate (%)</label>
                                        <input type="number" class="form-control" id="fertilityRate" min="0" max="100" placeholder="Fertility percentage">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="incubationStatus">Incubation Status</label>
                                        <select class="form-control" id="incubationStatus">
                                            <option value="incubating">Incubating</option>
                                            <option value="hatched">Hatched</option>
                                            <option value="failed">Failed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        

                        <!-- Task Fields -->
                        <div id="taskFields" style="display: none;">
                            <hr>
                            <h6 class="text-info">Task Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="taskTitle">Task Title</label>
                                        <input type="text" class="form-control" id="taskTitle" placeholder="Enter task title">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="taskPriority">Priority</label>
                                        <select class="form-control" id="taskPriority">
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="taskDescription">Task Description</label>
                                <textarea class="form-control" id="taskDescription" rows="3" placeholder="Describe the task..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dueDate">Due Date</label>
                                        <input type="date" class="form-control" id="dueDate">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="taskStatus">Status</label>
                                        <select class="form-control" id="taskStatus">
                                            <option value="pending">Pending</option>
                                            <option value="in-progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="recordNotes">General Notes</label>
                            <textarea class="form-control" id="recordNotes" rows="3" placeholder="Additional notes about this record..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="saveRecor/config/database.phptn">Save Record</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Chart.js -->
    <script src="vendor/chart.js/Chart.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.js"></script>
    
    <!-- Dashboard Charts -->
    <script>
        // Dashboard charts rendering
        $(document).ready(function() {
            // Pie chart: hatching service rate distribution
            var hatchRateData = <?php echo json_encode($hatch_rate_distribution); ?>;
            var pieLabels = hatchRateData.map(function(item) { return item.category; });
            var pieValues = hatchRateData.map(function(item) { return item.count; });
            
            // Define colors for different rate categories
            var pieColors = [
                'rgba(40, 167, 69, 0.85)',   // Excellent - Green
                'rgba(23, 162, 184, 0.85)',  // Good - Info
                'rgba(255, 193, 7, 0.85)',   // Fair - Warning
                'rgba(220, 53, 69, 0.85)',   // Poor - Danger
                'rgba(108, 117, 125, 0.85)'  // Very Poor - Secondary
            ];
            
            var pieCtx = document.getElementById('hatchRateDistributionPie').getContext('2d');
            new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieValues,
                        backgroundColor: pieColors.slice(0, pieLabels.length),
                        borderColor: pieColors.slice(0, pieLabels.length).map(color => color.replace('0.85', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { 
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    var value = context.parsed || 0;
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = ((value / total) * 100).toFixed(1);
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });

            // Bar chart: egg condition vs average weight
            var conditionData = <?php echo json_encode($condition_weights); ?>;
            var barLabels = conditionData.map(function(item) { return item.condition; });
            var barWeights = conditionData.map(function(item) { return item.avgWeight; });
            var barCounts = conditionData.map(function(item) { return item.count; });
            
            var barCtx = document.getElementById('conditionWeightBar').getContext('2d');
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: barLabels,
                    datasets: [{
                        label: 'Average Weight (g)',
                        data: barWeights,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                afterLabel: function(context) {
                                    var index = context.dataIndex;
                                    var count = barCounts[index];
                                    return 'Count: ' + count + ' eggs';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            title: { 
                                display: true, 
                                text: 'Average Weight (g)' 
                            } 
                        }
                    }
                }
            });

        // Add Record Modal Functionality
            // Set default date to today
            $('#recordDate').val(new Date().toISOString().split('T')[0]);

            // Show/hide fields based on record type selection
            $('#recordType').change(function() {
                var selectedType = $(this).val();
                
                // Hide all field sections first
                $('#matingPairFields, #eggDataFields, #taskFields').hide();
                
                // Show relevant fields based on selection
                switch(selectedType) {
                    case 'mating_pair':
                        $('#matingPairFields').show();
                        break;
                    case 'egg_data':
                        $('#eggDataFields').show();
                        break;
                    case 'task':
                        $('#taskFields').show();
                        break;
                }
            });

            // Save Record functionality
            $('#saveRecor/config/database.phptn').click(function() {
                var recordType = $('#recordType').val();
                var recordDate = $('#recordDate').val();
                
                if (!recordType || !recordDate) {
                    alert('Please fill in all required fields.');
                    return;
                }

                // Collect form data based on record type
                var formData = {
                    type: recordType,
                    date: recordDate,
                    notes: $('#recordNotes').val()
                };

                // Add specific data based on record type
                switch(recordType) {
                    case 'mating_pair':
                        formData.maleTortoise = $('#maleTortoise').val();
                        formData.femaleTortoise = $('#femaleTortoise').val();
                        formData.pairStatus = $('#pairStatus').val();
                        break;
                    case 'egg_data':
                        formData.batchID = $('#batchID').val();
                        formData.totalEggs = $('#totalEggs').val();
                        formData.fertilityRate = $('#fertilityRate').val();
                        formData.incubationStatus = $('#incubationStatus').val();
                        break;
                    case 'task':
                        formData.taskTitle = $('#taskTitle').val();
                        formData.taskPriority = $('#taskPriority').val();
                        formData.taskDescription = $('#taskDescription').val();
                        formData.dueDate = $('#dueDate').val();
                        formData.taskStatus = $('#taskStatus').val();
                        break;
                }

                // Simulate saving the record
                console.log('Saving record:', formData);
                
                // Show success message
                alert('Record saved successfully!');
                
                // Reset form and close modal
                $('#addRecordForm')[0].reset();
                $('#matingPairFields, #eggDataFields, #taskFields').hide();
                $('#addRecordModal').modal('hide');
                
                // Optionally refresh the page or update charts
                // location.reload();
            });

            // Reset form when modal is closed
            $('#addRecordModal').on('hidden.bs.modal', function() {
                $('#addRecordForm')[0].reset();
                $('#matingPairFields, #eggDataFields, #taskFields').hide();
                $('#recordDate').val(new Date().toISOString().split('T')[0]);
            });
        });
    </script>
</body>
</html>
