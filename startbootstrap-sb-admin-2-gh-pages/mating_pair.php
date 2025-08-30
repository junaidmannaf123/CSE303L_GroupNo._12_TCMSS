<?php
require_once __DIR__ . '/config/database.php';

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $cbreedingid = trim($_POST['cbreedingid']);
                $nincubationperiod = !empty($_POST['nincubationperiod']) ? (int)$_POST['nincubationperiod'] : null;
                $dstartdate = !empty($_POST['dstartdate']) ? $_POST['dstartdate'] : null;
                $denddate = !empty($_POST['denddate']) ? $_POST['denddate'] : null;
                $neggscount = !empty($_POST['neggscount']) ? (int)$_POST['neggscount'] : null;
                $nhatchingservicerate = !empty($_POST['nhatchingservicerate']) ? (int)$_POST['nhatchingservicerate'] : null;
                $cstaffid = !empty($_POST['cstaffid']) ? trim($_POST['cstaffid']) : null;
                $ctortoiseid = !empty($_POST['ctortoiseid']) ? trim($_POST['ctortoiseid']) : null;
                
                if ($cbreedingid) {
                    $stmt = $mysqli->prepare("INSERT INTO tblbreedingrecord (cbreedingid, nincubationperiod, dstartdate, denddate, neggscount, nhatchingservicerate, cstaffid, ctortoiseid) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('sissiiss', $cbreedingid, $nincubationperiod, $dstartdate, $denddate, $neggscount, $nhatchingservicerate, $cstaffid, $ctortoiseid);
                    if ($stmt->execute()) {
                        $message = "Breeding record added successfully!";
                        $message_type = "success";
                    } else {
                        $message = "Error adding breeding record: " . $stmt->error;
                        $message_type = "danger";
                    }
                } else {
                    $message = "Breeding ID is required!";
                    $message_type = "danger";
                }
                break;
                
            case 'update':
                $cbreedingid = trim($_POST['cbreedingid']);
                $nincubationperiod = !empty($_POST['nincubationperiod']) ? (int)$_POST['nincubationperiod'] : null;
                $dstartdate = !empty($_POST['dstartdate']) ? $_POST['dstartdate'] : null;
                $denddate = !empty($_POST['denddate']) ? $_POST['denddate'] : null;
                $neggscount = !empty($_POST['neggscount']) ? (int)$_POST['neggscount'] : null;
                $nhatchingservicerate = !empty($_POST['nhatchingservicerate']) ? (int)$_POST['nhatchingservicerate'] : null;
                $cstaffid = !empty($_POST['cstaffid']) ? trim($_POST['cstaffid']) : null;
                $ctortoiseid = !empty($_POST['ctortoiseid']) ? trim($_POST['ctortoiseid']) : null;
                
                $stmt = $mysqli->prepare("UPDATE tblbreedingrecord SET nincubationperiod=?, dstartdate=?, denddate=?, neggscount=?, nhatchingservicerate=?, cstaffid=?, ctortoiseid=? WHERE cbreedingid=?");
                $stmt->bind_param('issiiss', $nincubationperiod, $dstartdate, $denddate, $neggscount, $nhatchingservicerate, $cstaffid, $ctortoiseid, $cbreedingid);
                if ($stmt->execute()) {
                    $message = "Breeding record updated successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error updating breeding record: " . $stmt->error;
                    $message_type = "danger";
                }
                break;
                
            case 'delete':
                $cbreedingid = trim($_POST['cbreedingid']);
                $stmt = $mysqli->prepare("DELETE FROM tblbreedingrecord WHERE cbreedingid=?");
                $stmt->bind_param('s', $cbreedingid);
                if ($stmt->execute()) {
                    $message = "Breeding record deleted successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error deleting breeding record: " . $stmt->error;
                    $message_type = "danger";
                }
                break;
        }
    }
}

// Fetch all breeding records
$breeding_records = [];
$stmt = $mysqli->prepare("SELECT cbreedingid, nincubationperiod, dstartdate, denddate, neggscount, nhatchingservicerate, cstaffid, ctortoiseid FROM tblbreedingrecord ORDER BY cbreedingid");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $breeding_records[] = $row;
}

// Calculate statistics
$total_records = count($breeding_records);
$total_eggs = 0;
$avg_hatch_rate = 0;
$avg_incubation_period = 0;
$total_hatch_rate = 0;
$total_incubation_period = 0;
$hatch_rate_count = 0;
$incubation_count = 0;

foreach ($breeding_records as $record) {
    if ($record['neggscount'] && $record['neggscount'] > 0) {
        $total_eggs += $record['neggscount'];
    }
    if ($record['nhatchingservicerate'] && $record['nhatchingservicerate'] > 0) {
        $total_hatch_rate += $record['nhatchingservicerate'];
        $hatch_rate_count++;
    }
    if ($record['nincubationperiod'] && $record['nincubationperiod'] > 0) {
        $total_incubation_period += $record['nincubationperiod'];
        $incubation_count++;
    }
}

$avg_hatch_rate = $hatch_rate_count > 0 ? round($total_hatch_rate / $hatch_rate_count, 1) : 0;
$avg_incubation_period = $incubation_count > 0 ? round($total_incubation_period / $incubation_count, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Tortoise Mating Pair Management">
    <meta name="author" content="">
    <title>Tortoise Conservation - Mating Pair Management</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="BSDashboard.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-turtle"></i>
                </div>
                <div class="sidebar-brand-text mx-3">TCMSS</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link" href="BSDashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Breeding Tools</div>
            <li class="nav-item active">
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
                    <h4 class="ml-3 mt-2 text-success font-weight-bold d-inline-block">Mating Pair Management</h4>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Specialist Name</span>
                                <i class="fas fa-user fa-2x text-success img-profile rounded-circle"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Profile</a>
                                <a class="dropdown-item" href="#"><i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Settings</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Logout</a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Mating Pair Management</h1>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm" data-toggle="modal" data-target="#addMatingPairModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Pair
                        </button>
                    </div>

                    <!-- Message Display -->
                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Breeding Records</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_records; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-heart fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Eggs</div>
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
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg Hatch Rate</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $avg_hatch_rate; ?>%</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Incubation Period</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $avg_incubation_period; ?> days</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mating Pairs Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-heart"></i> Current Mating Pairs</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Breeding ID</th>
                                            <th>Incubation Period (days)</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Eggs Count</th>
                                            <th>Hatching Service Rate (%)</th>
                                            <th>Staff ID</th>
                                            <th>Tortoise ID</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($breeding_records as $record): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['cbreedingid']); ?></td>
                                            <td><?php echo $record['nincubationperiod'] ? htmlspecialchars($record['nincubationperiod']) : '-'; ?></td>
                                            <td><?php echo $record['dstartdate'] ? htmlspecialchars($record['dstartdate']) : '-'; ?></td>
                                            <td><?php echo $record['denddate'] ? htmlspecialchars($record['denddate']) : '-'; ?></td>
                                            <td><?php echo $record['neggscount'] ? htmlspecialchars($record['neggscount']) : '-'; ?></td>
                                            <td>
                                                <?php if ($record['nhatchingservicerate']): ?>
                                                    <span class="badge badge-<?php echo $record['nhatchingservicerate'] >= 70 ? 'success' : ($record['nhatchingservicerate'] >= 50 ? 'warning' : 'danger'); ?>">
                                                        <?php echo htmlspecialchars($record['nhatchingservicerate']); ?>%
                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $record['cstaffid'] ? htmlspecialchars($record['cstaffid']) : '-'; ?></td>
                                            <td><?php echo $record['ctortoiseid'] ? htmlspecialchars($record['ctortoiseid']) : '-'; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary mr-2" data-toggle="modal" data-target="#editMatingPairModal" 
                                                        onclick="populateEditForm('<?php echo htmlspecialchars($record['cbreedingid']); ?>', '<?php echo htmlspecialchars($record['nincubationperiod']); ?>', '<?php echo htmlspecialchars($record['dstartdate']); ?>', '<?php echo htmlspecialchars($record['denddate']); ?>', '<?php echo htmlspecialchars($record['neggscount']); ?>', '<?php echo htmlspecialchars($record['nhatchingservicerate']); ?>', '<?php echo htmlspecialchars($record['cstaffid']); ?>', '<?php echo htmlspecialchars($record['ctortoiseid']); ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this breeding record?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="cbreedingid" value="<?php echo htmlspecialchars($record['cbreedingid']); ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
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

    <!-- Add Mating Pair Modal -->
    <div class="modal fade" id="addMatingPairModal" tabindex="-1" role="dialog" aria-labelledby="addMatingPairModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMatingPairModalLabel">Add New Mating Pair</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cbreedingid">Breeding ID</label>
                                    <input type="text" class="form-control" id="cbreedingid" name="cbreedingid" placeholder="BRXXX" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dstartdate">Start Date</label>
                                    <input type="date" class="form-control" id="dstartdate" name="dstartdate">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nincubationperiod">Incubation Period (days)</label>
                                    <input type="number" class="form-control" id="nincubationperiod" name="nincubationperiod" min="1" max="365">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="denddate">End Date</label>
                                    <input type="date" class="form-control" id="denddate" name="denddate">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="neggscount">Eggs Count</label>
                                    <input type="number" class="form-control" id="neggscount" name="neggscount" min="1" max="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nhatchingservicerate">Hatching Service Rate (%)</label>
                                    <input type="number" class="form-control" id="nhatchingservicerate" name="nhatchingservicerate" min="0" max="100">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cstaffid">Staff ID</label>
                                    <input type="text" class="form-control" id="cstaffid" name="cstaffid" placeholder="e.g., SM001">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ctortoiseid">Tortoise ID</label>
                                    <input type="text" class="form-control" id="ctortoiseid" name="ctortoiseid" placeholder="e.g., 001">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Pair</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Mating Pair Modal -->
    <div class="modal fade" id="editMatingPairModal" tabindex="-1" role="dialog" aria-labelledby="editMatingPairModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMatingPairModalLabel">Edit Mating Pair</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="cbreedingid" id="edit_cbreedingid">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_nincubationperiod">Incubation Period (days)</label>
                                    <input type="number" class="form-control" id="edit_nincubationperiod" name="nincubationperiod" min="1" max="365">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_dstartdate">Start Date</label>
                                    <input type="date" class="form-control" id="edit_dstartdate" name="dstartdate">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_denddate">End Date</label>
                                    <input type="date" class="form-control" id="edit_denddate" name="denddate">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_neggscount">Eggs Count</label>
                                    <input type="number" class="form-control" id="edit_neggscount" name="neggscount" min="1" max="100">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_nhatchingservicerate">Hatching Service Rate (%)</label>
                                    <input type="number" class="form-control" id="edit_nhatchingservicerate" name="nhatchingservicerate" min="0" max="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_cstaffid">Staff ID</label>
                                    <input type="text" class="form-control" id="edit_cstaffid" name="cstaffid">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_ctortoiseid">Tortoise ID</label>
                            <input type="text" class="form-control" id="edit_ctortoiseid" name="ctortoiseid">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Pair</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.js"></script>
    
    <script>
        function populateEditForm(cbreedingid, nincubationperiod, dstartdate, denddate, neggscount, nhatchingservicerate, cstaffid, ctortoiseid) {
            document.getElementById('edit_cbreedingid').value = cbreedingid;
            document.getElementById('edit_nincubationperiod').value = nincubationperiod;
            document.getElementById('edit_dstartdate').value = dstartdate;
            document.getElementById('edit_denddate').value = denddate;
            document.getElementById('edit_neggscount').value = neggscount;
            document.getElementById('edit_nhatchingservicerate').value = nhatchingservicerate;
            document.getElementById('edit_cstaffid').value = cstaffid;
            document.getElementById('edit_ctortoiseid').value = ctortoiseid;
        }
    </script>
</body>
</html>
