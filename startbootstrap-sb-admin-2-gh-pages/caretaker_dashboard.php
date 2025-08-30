<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['staff_id']) || !in_array($_SESSION['role'], ['Tortoise Caretaker', 'Manager'])) {
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

// Fetch dashboard statistics from database
try {
    // Get total tortoises count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_tortoises FROM tbltortoise");
    $stmt->execute();
    $totalTortoises = $stmt->fetch()['total_tortoises'];

    // Get today's feeding schedules count
    $stmt = $pdo->prepare("SELECT COUNT(*) as today_feedings FROM tblfeedingschedule WHERE DATE(ddate) = CURDATE()");
    $stmt->execute();
    $todayFeedings = $stmt->fetch()['today_feedings'];

    // Get total feeding schedules count (since no status column exists)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_feedings FROM tblfeedingschedule");
    $stmt->execute();
    $totalFeedings = $stmt->fetch()['total_feedings'];

    // Get total medical records count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_medical FROM tblmedicalrecords");
    $stmt->execute();
    $totalMedical = $stmt->fetch()['total_medical'];

    // Get recent medical records (last 7 days)
    $stmt = $pdo->prepare("SELECT COUNT(*) as recent_medical FROM tblmedicalrecords WHERE DATE(ddate) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $stmt->execute();
    $recentMedical = $stmt->fetch()['recent_medical'];

    // Get tortoise health conditions - simplified approach
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_tortoises
        FROM tbltortoise
    ");
    $stmt->execute();
    $totalTortoisesCount = $stmt->fetch()['total_tortoises'];

    // Get tortoises with recent medical issues (last 30 days)
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT m.ctortoiseid) as sick_count
        FROM tblmedicalrecords m
        WHERE m.ddate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND (m.cdiagnosis LIKE '%infection%' OR m.cdiagnosis LIKE '%sick%' OR m.cdiagnosis LIKE '%disease%')
    ");
    $stmt->execute();
    $sickTortoises = $stmt->fetch()['sick_count'];

    // Calculate healthy tortoises
    $healthyTortoises = $totalTortoisesCount - $sickTortoises;
    $needsAttention = $sickTortoises;

    // Get tortoises under observation (recent medical records)
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT m.ctortoiseid) as under_observation
        FROM tblmedicalrecords m
        WHERE m.ddate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute();
    $underObservation = $stmt->fetch()['under_observation'];

    // Get today's feeding schedules (without status)
    $stmt = $pdo->prepare("
        SELECT 
            cfeedingid,
            TIME_FORMAT(dtime, '%H:%i') as feeding_time,
            cdietnotes,
            cenclosureid
        FROM tblfeedingschedule 
        WHERE DATE(ddate) = CURDATE()
        ORDER BY dtime ASC
        LIMIT 5
    ");
    $stmt->execute();
    $todaySchedules = $stmt->fetchAll();

    // Get recent medical alerts
    $stmt = $pdo->prepare("
        SELECT 
            m.ctortoiseid,
            t.cname as tortoise_name,
            m.cdiagnosis,
            m.ddate,
            m.ctreatment
        FROM tblmedicalrecords m
        INNER JOIN tbltortoise t ON m.ctortoiseid = t.ctortoiseid
        WHERE m.ddate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY m.ddate DESC
        LIMIT 3
    ");
    $stmt->execute();
    $recentAlerts = $stmt->fetchAll();

    // Since no status column exists, we'll show all today's feedings as pending
    $totalTodayFeedings = count($todaySchedules);
    $completedTodayFeedings = 0; // No status tracking available
    $feedingProgress = 0; // No progress tracking without status

} catch(PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    // Set default values if database error occurs
    $totalTortoises = 0;
    $todayFeedings = 0;
    $totalFeedings = 0;
    $totalMedical = 0;
    $recentMedical = 0;
    $healthyTortoises = 0;
    $needsAttention = 0;
    $underObservation = 0;
    $todaySchedules = [];
    $recentAlerts = [];
    $feedingProgress = 0;
    $totalTodayFeedings = 0;
    $completedTodayFeedings = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tortoise Caretaker Dashboard</title>

  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
  <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">

  <div id="wrapper">

    <ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="caretaker_dashboard.php">
        <div class="sidebar-brand-text mx-3">🐢TCCMS</div>
      </a>

      <hr class="sidebar-divider my-0">

      <li class="nav-item active">
        <a class="nav-link" href="caretaker_dashboard.php">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span></a>
      </li>

      <hr class="sidebar-divider">

      <li class="nav-item">
        <a class="nav-link" href="caretaker_tasks.php">
          <i class="fas fa-tasks"></i>
          <span>Tasks</span></a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="feeding.php">
          <i class="fas fa-seedling"></i>
          <span>Feeding Schedule</span></a>
      </li>

      <hr class="sidebar-divider d-none d-md-block">
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
       
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
          <h1 class="h4 text-gray-800">Caretaker Dashboard</h1>
          <ul class="navbar-nav ml-auto">
            <div class="topbar-divider d-none d-sm-block"></div>
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                  <?php echo htmlspecialchars($_SESSION['staff_name']); ?> 
                  <span class="badge badge-success"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                </span>
                <i class="fas fa-user fa-2x text-success img-profile rounded-circle"></i>
              </a>
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Profile
                </a>
                <a class="dropdown-item" href="#">
                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                  Settings
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="logout.php">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logout
                </a>
              </div>
            </li>
          </ul>
        </nav>

        <div class="container-fluid">

          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Welcome, <?php echo htmlspecialchars($_SESSION['staff_name']); ?>!</h1>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
              <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
            </a>
          </div>


          <div class="row">

            <?php if (isset($error_message)): ?>
              <div class="col-12 mb-4">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <?php echo $error_message; ?>
                  <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                  </button>
                </div>
              </div>
            <?php endif; ?>

            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Tortoises</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalTortoises; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-paw fa-2x text-success"></i>
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
                      <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today's Feedings</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $todayFeedings; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-seedling fa-2x text-success"></i>
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
                      <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Feedings</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalFeedings; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-clock fa-2x text-info"></i>
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
                      <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Recent Medical</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $recentMedical; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-stethoscope fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <div class="row">
            <div class="col-lg-12 mb-4">
              <div class="card shadow">
                <div class="card-header py-3 bg-success text-white">
                  <h6 class="m-0 font-weight-bold">Tortoise Condition Summary</h6>
                </div>
                <div class="card-body">
                  <div class="row text-center">
                    <div class="col-md-4">
                      <div class="text-primary font-weight-bold">Healthy</div>
                      <div class="h4 text-gray-800"><?php echo $healthyTortoises; ?></div>
                    </div>
                    <div class="col-md-4">
                      <div class="text-warning font-weight-bold">Needs Attention</div>
                      <div class="h4 text-gray-800"><?php echo $needsAttention; ?></div>
                    </div>
                    <div class="col-md-4">
                      <div class="text-danger font-weight-bold">Under Observation</div>
                      <div class="h4 text-gray-800"><?php echo $underObservation; ?></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-6 mb-4">
              <div class="card shadow">
                <div class="card-header py-3 bg-success text-white">
                  <h6 class="m-0 font-weight-bold">Today's Feeding Schedule</h6>
                </div>
                <div class="card-body">
                  <?php if (!empty($todaySchedules)): ?>
                    <ul class="list-group">
                      <?php foreach ($todaySchedules as $schedule): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <?php echo htmlspecialchars($schedule['feeding_time']); ?> - 
                          <?php echo htmlspecialchars($schedule['cdietnotes'] ?: 'Feeding'); ?> 
                          (<?php echo htmlspecialchars($schedule['cenclosureid']); ?>)
                          <span class="badge badge-secondary badge-pill">Scheduled</span>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php else: ?>
                    <div class="text-center text-muted">
                      <i class="fas fa-calendar-times fa-3x mb-3"></i>
                      <p>No feeding schedules for today</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="col-lg-6 mb-4">
              <div class="card shadow">
                <div class="card-header py-3 bg-success text-white">
                  <h6 class="m-0 font-weight-bold">Recent Medical Alerts</h6>
                </div>
                <div class="card-body">
                  <?php if (!empty($recentAlerts)): ?>
                    <?php foreach ($recentAlerts as $alert): ?>
                      <div class="alert alert-<?php echo strpos(strtolower($alert['cdiagnosis']), 'infection') !== false || strpos(strtolower($alert['cdiagnosis']), 'sick') !== false ? 'danger' : 'warning'; ?>" role="alert">
                        <i class="fas fa-<?php echo strpos(strtolower($alert['cdiagnosis']), 'infection') !== false || strpos(strtolower($alert['cdiagnosis']), 'sick') !== false ? 'exclamation-circle' : 'exclamation-triangle'; ?>"></i> 
                        <strong><?php echo htmlspecialchars($alert['tortoise_name']); ?></strong> - 
                        <?php echo htmlspecialchars($alert['cdiagnosis']); ?>
                        <br><small class="text-muted"><?php echo date('M d, Y', strtotime($alert['ddate'])); ?> - <?php echo htmlspecialchars($alert['ctreatment']); ?></small>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <div class="text-center text-muted">
                      <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                      <p>No recent medical alerts</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

          </div>

          
          <div class="row">
            <div class="col-lg-12 mb-4">
              <div class="card shadow">
                <div class="card-header py-3 bg-success text-white">
                  <h6 class="m-0 font-weight-bold">Feeding Schedule Overview</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <h6>Today's Feedings</h6>
                      <div class="progress mb-3">
                        <div class="progress-bar bg-info" 
                             role="progressbar" 
                             style="width: 100%" 
                             aria-valuenow="100"
                             aria-valuemin="0" 
                             aria-valuemax="100">
                          <?php echo $totalTodayFeedings; ?> Scheduled
                        </div>
                      </div>
                      <small class="text-muted">
                        <?php echo $totalTodayFeedings; ?> feeding(s) scheduled for today
                      </small>
                    </div>
                    <div class="col-md-6">
                      <h6>Quick Stats</h6>
                      <div class="row text-center">
                        <div class="col-6">
                          <div class="text-info">
                            <i class="fas fa-calendar fa-2x"></i>
                            <div class="mt-2"><?php echo $totalFeedings; ?></div>
                            <small>Total Feedings</small>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="text-success">
                            <i class="fas fa-seedling fa-2x"></i>
                            <div class="mt-2"><?php echo $todayFeedings; ?></div>
                            <small>Today</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <footer class="sticky-footer bg-white">
        <div class="container my-auto text-center">
          <span>© 2025 Tortoise Conservation Center</span>
        </div>
      </footer>
    </div>
  </div>

  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/sb-admin-2.min.js"></script>

</body>

</html>
