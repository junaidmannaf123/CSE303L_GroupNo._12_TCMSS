<?php
session_start();
require_once 'config/database.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Tortoise Conservation Management System - Egg Incubator">
    <meta name="author" content="">
    <title>Tortoise Conservation - Egg Incubator</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="Egg_Incubator.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-turtle"></i>
                </div>
                <div class="sidebar-brand-text mx-3">TCMSS</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link" href="Egg_Incubator.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Incubator Tools</div>
            <li class="nav-item active">
                <a class="nav-link" href="Egg_Incubator.php">
                    <i class="fas fa-fw fa-egg"></i>
                    <span>Egg Incubator</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Environment_Monitor.php">
                    <i class="fas fa-fw fa-leaf"></i>
                    <span>Environment Monitor</span></a>
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
                    <h4 class="ml-3 mt-2 text-warning font-weight-bold d-inline-block">Egg Incubator</h4>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                  <?php echo htmlspecialchars($_SESSION['staff_name']); ?> 
                                  <span class="badge badge-success"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                                </span>
                                <i class="fas fa-user fa-2x text-success img-profile rounded-circle"></i>
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
                    <!-- Egg Incubator Content -->
                    <div class="row">
                        <!-- Incubator Status -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Current Temperature</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">32°C</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-thermometer-half fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Current Humidity</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">55%</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-tint fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Egg Count</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">12</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-egg fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Incubator Data Update Section -->
                    <div class="row">
                        <div class="col-xl-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-egg mr-2"></i>Update Incubator Data</h6>
                                </div>
                                <div class="card-body">
                                    <form id="incubatorDataForm">
                                        <div class="form-group">
                                            <label for="incubatorTemp">Temperature (°C)</label>
                                            <input type="number" class="form-control" id="incubatorTemp" name="incubatorTemp" min="20" max="40" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="incubatorHumidity">Humidity (%)</label>
                                            <input type="number" class="form-control" id="incubatorHumidity" name="incubatorHumidity" min="0" max="100" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="eggCount">Egg Count</label>
                                            <input type="number" class="form-control" id="eggCount" name="eggCount" min="0" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="incubatorNotes">Notes</label>
                                            <textarea class="form-control" id="incubatorNotes" name="incubatorNotes" rows="2"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-warning text-white"><i class="fas fa-save mr-1"></i>Update Data</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Incubator Logs -->
                        <div class="col-xl-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-list-alt mr-2"></i>Incubator Logs</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Date/Time</th>
                                                    <th>Temperature (°C)</th>
                                                    <th>Humidity (%)</th>
                                                    <th>Egg Count</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>2025-07-31 09:00</td>
                                                    <td>32</td>
                                                    <td>55</td>
                                                    <td>12</td>
                                                    <td><span class="badge badge-success">Normal</span></td>
                                                </tr>
                                                <tr>
                                                    <td>2025-07-31 08:00</td>
                                                    <td>33</td>
                                                    <td>50</td>
                                                    <td>12</td>
                                                    <td><span class="badge badge-warning">Warning</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button class="btn btn-outline-success btn-sm mt-2"><i class="fas fa-file-download mr-1"></i>Download Logs</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Incubator Chart -->
                    <div class="row">
                        <div class="col-xl-12 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-chart-line mr-2"></i>Incubator Temperature Trend</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="incubatorTrendChart" height="80"></canvas>
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
    <script>
        // Example Chart.js for Incubator Temperature Trend
        var ctx = document.getElementById('incubatorTrendChart').getContext('2d');
        var incubatorTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['08:00', '09:00', '10:00', '11:00', '12:00'],
                datasets: [
                    {
                        label: 'Temperature (°C)',
                        data: [33, 32, 32, 33, 32],
                        borderColor: '#f6c23e',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Example form handler (no backend, just prevent reload)
        document.getElementById('incubatorDataForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Incubator data updated!');
            this.reset();
        });
    </script>