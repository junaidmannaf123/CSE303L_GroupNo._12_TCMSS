<?php
require_once __DIR__ . '/config/database.php';

// Check database connection
if (!$mysqli || $mysqli->connect_error) {
    die("Database connection failed: " . ($mysqli ? $mysqli->connect_error : "Unknown error"));
}

// Check if the table exists
$table_check = $mysqli->query("SHOW TABLES LIKE 'tbleggdetails'");
if ($table_check->num_rows === 0) {
    die("Error: Table 'tbleggdetails' does not exist. Please check your database setup.");
}

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        
        switch ($_POST['action']) {
            case 'add':
                $ceggid = trim($_POST['ceggid']);
                $nweight = !empty($_POST['nweight']) ? (int)$_POST['nweight'] : null;
                $nlength = !empty($_POST['nlength']) ? (int)$_POST['nlength'] : null;
                $nwidth = !empty($_POST['nwidth']) ? (int)$_POST['nwidth'] : null;
                $ceggcondition = !empty($_POST['ceggcondition']) ? trim($_POST['ceggcondition']) : null;
                $cincubatorid = !empty($_POST['cincubatorid']) ? trim($_POST['cincubatorid']) : null;
                $cbreedingid = !empty($_POST['cbreedingid']) ? trim($_POST['cbreedingid']) : null;
                
                if ($ceggid) {
                    // Validate egg ID format (should start with E and followed by numbers)
                    if (!preg_match('/^E\d+$/', $ceggid)) {
                        if ($isAjax) {
                            send_json(['success' => false, 'message' => "Egg ID must start with 'E' followed by numbers (e.g., E001, E002)"]);
                        } else {
                            $message = "Error: Egg ID must start with 'E' followed by numbers (e.g., E001, E002)";
                            $message_type = "danger";
                        }
                    } else {
                        // Check if egg ID already exists
                        $check_stmt = $mysqli->prepare("SELECT ceggid FROM tbleggdetails WHERE ceggid = ?");
                        $check_stmt->bind_param('s', $ceggid);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();
                        
                        if ($check_result->num_rows > 0) {
                            if ($isAjax) {
                                send_json(['success' => false, 'message' => "Egg ID '$ceggid' already exists! Please use a different ID."]);
                            } else {
                                $message = "Error: Egg ID '$ceggid' already exists! Please use a different ID.";
                                $message_type = "danger";
                            }
                        } else {
                            $stmt = $mysqli->prepare("INSERT INTO tbleggdetails (ceggid, nweight, nlength, nwidth, ceggcondition, cincubatorid, cbreedingid) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            if ($stmt === false) {
                                if ($isAjax) {
                                    send_json(['success' => false, 'message' => "Error preparing statement: " . $mysqli->error]);
                                } else {
                                    $message = "Error preparing statement: " . $mysqli->error;
                                    $message_type = "danger";
                                }
                            } else {
                                $stmt->bind_param('siiisss', $ceggid, $nweight, $nlength, $nwidth, $ceggcondition, $cincubatorid, $cbreedingid);
                                if ($stmt->execute()) {
                                    $nextEggId = getNextEggId($mysqli);
                                    if ($isAjax) {
                                        send_json(['success' => true, 'message' => "Egg '$ceggid' added successfully!", 'nextEggId' => $nextEggId]);
                                    } else {
                                        $details = [];
                                        if ($nweight) $details[] = "Weight: {$nweight}g";
                                        if ($nlength) $details[] = "Length: {$nlength}mm";
                                        if ($nwidth) $details[] = "Width: {$nwidth}mm";
                                        if ($ceggcondition) $details[] = "Condition: {$ceggcondition}";
                                        
                                        $message = "Egg '$ceggid' added successfully!";
                                        if (!empty($details)) {
                                            $message .= " Details: " . implode(", ", $details);
                                        }
                                        $message_type = "success";
                                    }
                                } else {
                                    if ($isAjax) {
                                        send_json(['success' => false, 'message' => "Error adding egg: " . $stmt->error]);
                                    } else {
                                        $message = "Error adding egg: " . $stmt->error . ". Please check your database connection and try again.";
                                        $message_type = "danger";
                                    }
                                }
                                $stmt->close();
                            }
                        }
                        $check_stmt->close();
                    }
                } else {
                    if ($isAjax) {
                        send_json(['success' => false, 'message' => "Egg ID is required!"]);
                    } else {
                        $message = "Egg ID is required!";
                        $message_type = "danger";
                    }
                }
                break;
                
            case 'update':
                $ceggid = trim($_POST['ceggid']);
                $nweight = !empty($_POST['nweight']) ? (int)$_POST['nweight'] : null;
                $nlength = !empty($_POST['nlength']) ? (int)$_POST['nlength'] : null;
                $nwidth = !empty($_POST['nwidth']) ? (int)$_POST['nwidth'] : null;
                $ceggcondition = !empty($_POST['ceggcondition']) ? trim($_POST['ceggcondition']) : null;
                $cincubatorid = !empty($_POST['cincubatorid']) ? trim($_POST['cincubatorid']) : null;
                $cbreedingid = !empty($_POST['cbreedingid']) ? trim($_POST['cbreedingid']) : null;
                
                $stmt = $mysqli->prepare("UPDATE tbleggdetails SET nweight=?, nlength=?, nwidth=?, ceggcondition=?, cincubatorid=?, cbreedingid=? WHERE ceggid=?");
                $stmt->bind_param('iiissss', $nweight, $nlength, $nwidth, $ceggcondition, $cincubatorid, $cbreedingid, $ceggid);
                if ($stmt->execute()) {
                    if ($isAjax) {
                        send_json(['success' => true, 'message' => "Egg '$ceggid' updated successfully!"]);
                    } else {
                        $message = "Egg '$ceggid' updated successfully!";
                        $message_type = "success";
                    }
                } else {
                    if ($isAjax) {
                        send_json(['success' => false, 'message' => "Error updating egg: " . $stmt->error]);
                    } else {
                        $message = "Error updating egg: " . $stmt->error . ". Please check your database connection and try again.";
                        $message_type = "danger";
                    }
                }
                $stmt->close();
                break;
                
            case 'delete':
                $ceggid = trim($_POST['ceggid']);
                $stmt = $mysqli->prepare("DELETE FROM tbleggdetails WHERE ceggid=?");
                $stmt->bind_param('s', $ceggid);
                if ($stmt->execute()) {
                    if ($isAjax) {
                        send_json(['success' => true, 'message' => "Egg '$ceggid' deleted successfully!"]);
                    } else {
                        $message = "Egg '$ceggid' deleted successfully!";
                        $message_type = "success";
                    }
                } else {
                    if ($isAjax) {
                        send_json(['success' => false, 'message' => "Error deleting egg: " . $stmt->error]);
                    } else {
                        $message = "Error deleting egg: " . $stmt->error . ". Please check your database connection and try again.";
                        $message_type = "danger";
                    }
                }
                $stmt->close();
                break;
                
            case 'getNextId':
                $nextEggId = getNextEggId($mysqli);
                if ($isAjax) {
                    send_json(['success' => true, 'nextEggId' => $nextEggId]);
                }
                break;
        }
    }
}

// Fetch all egg data
$eggs = [];
$stmt = $mysqli->prepare("SELECT ceggid, nweight, nlength, nwidth, ceggcondition, cincubatorid, cbreedingid FROM tbleggdetails ORDER BY ceggid");
if ($stmt === false) {
    die("Error preparing statement: " . $mysqli->error);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $eggs[] = $row;
}
$stmt->close();

// Function to generate next available egg ID
function getNextEggId($mysqli) {
    $stmt = $mysqli->prepare("SELECT ceggid FROM tbleggdetails ORDER BY ceggid DESC LIMIT 1");
    if ($stmt === false) {
        return 'E001'; // Default if error
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastId = $row['ceggid'];
        // Extract number from ID (assuming format like E001, E002, etc.)
        if (preg_match('/E(\d+)/', $lastId, $matches)) {
            $nextNum = intval($matches[1]) + 1;
            return 'E' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }
    }
    $stmt->close();
    return 'E001'; // Default if no eggs exist
}

$nextEggId = getNextEggId($mysqli);

// Calculate statistics
$total_eggs = count($eggs);
$healthy_eggs = 0;
$problem_eggs = 0;
$total_weight = 0;
$weight_count = 0;

foreach ($eggs as $egg) {
    if ($egg['ceggcondition'] === 'Normal') {
        $healthy_eggs++;
    } else {
        $problem_eggs++;
    }
    if ($egg['nweight'] && $egg['nweight'] > 0) {
        $total_weight += $egg['nweight'];
        $weight_count++;
    }
}

$avg_weight = $weight_count > 0 ? round($total_weight / $weight_count, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Tortoise Conservation Management System - Egg Data">
    <meta name="author" content="">
    <title>Tortoise Conservation - Egg Data Management</title>
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
            <li class="nav-item">
                <a class="nav-link" href="mating_pair.php">
                    <i class="fas fa-heart"></i>
                    <span>Mating Pairs</span></a>
            </li>
            <li class="nav-item active">
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
                    <h4 class="ml-3 mt-2 text-success font-weight-bold d-inline-block">Egg Data Management</h4>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Specialist Name</span>
                                <i class="fas fa-user fa-2x text-success img-profile rounded-circle"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelle/config/database.phpy="userDropdown">
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
                        <h1 class="h3 mb-0 text-gray-800">Egg Data Records</h1>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm" data-toggle="modal" data-target="#addEggModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Egg
                        </button>
                    </div>

                    <!-- Message Display -->
                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <?php if ($message_type === 'success'): ?>
                            <br><small class="text-muted">Next available egg ID: <?php echo htmlspecialchars($nextEggId); ?></small>
                        <?php endif; ?>
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
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Eggs</div>
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
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Healthy Eggs</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $healthy_eggs; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Average Weight</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $avg_weight; ?>g</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-weight fa-2x text-gray-300"></i>
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
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Problem Eggs</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $problem_eggs; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Egg Data Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-egg"></i> Egg Records</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Egg ID</th>
                                            <th>Weight (g)</th>
                                            <th>Length (mm)</th>
                                            <th>Width (mm)</th>
                                            <th>Condition</th>
                                            <th>Incubator ID</th>
                                            <th>Breeding ID</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($eggs as $egg): ?>
                                        <tr data-egg-id="<?php echo htmlspecialchars($egg['ceggid']); ?>">
                                            <td><?php echo htmlspecialchars($egg['ceggid']); ?></td>
                                            <td><?php echo $egg['nweight'] ? htmlspecialchars($egg['nweight']) : '-'; ?></td>
                                            <td><?php echo $egg['nlength'] ? htmlspecialchars($egg['nlength']) : '-'; ?></td>
                                            <td><?php echo $egg['nwidth'] ? htmlspecialchars($egg['nwidth']) : '-'; ?></td>
                                            <td>
                                                <?php if ($egg['ceggcondition']): ?>
                                                    <span class="badge badge-<?php echo $egg['ceggcondition'] === 'Normal' ? 'success' : 'warning'; ?>">
                                                        <?php echo htmlspecialchars($egg['ceggcondition']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $egg['cincubatorid'] ? htmlspecialchars($egg['cincubatorid']) : '-'; ?></td>
                                            <td><?php echo $egg['cbreedingid'] ? htmlspecialchars($egg['cbreedingid']) : '-'; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary mr-2" data-toggle="modal" data-target="#editEggModal" 
                                                        onclick="populateEditForm('<?php echo htmlspecialchars($egg['ceggid']); ?>', '<?php echo htmlspecialchars($egg['nweight']); ?>', '<?php echo htmlspecialchars($egg['nlength']); ?>', '<?php echo htmlspecialchars($egg['nwidth']); ?>', '<?php echo htmlspecialchars($egg['ceggcondition']); ?>', '<?php echo htmlspecialchars($egg['cincubatorid']); ?>', '<?php echo htmlspecialchars($egg['cbreedingid']); ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteEgg('<?php echo htmlspecialchars($egg['ceggid']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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

    <!-- Add Egg Modal -->
    <div class="modal fade" id="addEggModal" tabindex="-1" role="dialog" aria-labelle/config/database.phpy="addEggModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEggModalLabel"><i class="fas fa-plus"></i> Add New Egg</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addEggForm" onsubmit="return addEgg(event)">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ceggid">Egg ID</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="ceggid" name="ceggid" placeholder="e.g., E001" value="<?php echo htmlspecialchars($nextEggId); ?>" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" onclick="generateNextId()">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Next available ID: <?php echo htmlspecialchars($nextEggId); ?></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nweight">Weight (g)</label>
                                    <input type="number" class="form-control" id="nweight" name="nweight" min="1" max="1000">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nlength">Length (mm)</label>
                                    <input type="number" class="form-control" id="nlength" name="nlength" min="1" max="200">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nwidth">Width (mm)</label>
                                    <input type="number" class="form-control" id="nwidth" name="nwidth" min="1" max="200">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ceggcondition">Egg Condition</label>
                                    <select class="form-control" id="ceggcondition" name="ceggcondition">
                                        <option value="">Select Condition</option>
                                        <option value="Normal">Normal</option>
                                        <option value="Cracked">Cracked</option>
                                        <option value="Soft Shell">Soft Shell</option>
                                        <option value="Thin Shell">Thin Shell</option>
                                        <option value="Deformed">Deformed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cincubatorid">Incubator ID</label>
                                    <input type="text" class="form-control" id="cincubatorid" name="cincubatorid" placeholder="e.g., INC001">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cbreedingid">Breeding ID</label>
                            <input type="text" class="form-control" id="cbreedingid" name="cbreedingid" placeholder="e.g., BR001">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Egg</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Egg Modal -->
    <div class="modal fade" id="editEggModal" tabindex="-1" role="dialog" aria-labelle/config/database.phpy="editEggModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEggModalLabel"><i class="fas fa-edit"></i> Edit Egg</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editEggForm" onsubmit="return updateEgg(event)">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="ceggid" id="edit_ceggid">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_nweight">Weight (g)</label>
                                    <input type="number" class="form-control" id="edit_nweight" name="nweight" min="1" max="1000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_nlength">Length (mm)</label>
                                    <input type="number" class="form-control" id="edit_nlength" name="nlength" min="1" max="200">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_nwidth">Width (mm)</label>
                                    <input type="number" class="form-control" id="edit_nwidth" name="nwidth" min="1" max="200">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_ceggcondition">Egg Condition</label>
                                    <select class="form-control" id="edit_ceggcondition" name="ceggcondition">
                                        <option value="">Select Condition</option>
                                        <option value="Normal">Normal</option>
                                        <option value="Cracked">Cracked</option>
                                        <option value="Soft Shell">Soft Shell</option>
                                        <option value="Thin Shell">Thin Shell</option>
                                        <option value="Deformed">Deformed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_cincubatorid">Incubator ID</label>
                                    <input type="text" class="form-control" id="edit_cincubatorid" name="cincubatorid">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_cbreedingid">Breeding ID</label>
                                    <input type="text" class="form-control" id="edit_cbreedingid" name="cbreedingid">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Egg</button>
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
        function populateEditForm(ceggid, nweight, nlength, nwidth, ceggcondition, cincubatorid, cbreedingid) {
            document.getElementById('edit_ceggid').value = ceggid;
            document.getElementById('edit_nweight').value = nweight;
            document.getElementById('edit_nlength').value = nlength;
            document.getElementById('edit_nwidth').value = nwidth;
            document.getElementById('edit_ceggcondition').value = ceggcondition;
            document.getElementById('edit_cincubatorid').value = cincubatorid;
            document.getElementById('edit_cbreedingid').value = cbreedingid;
        }
        
        function generateNextId() {
            // Fetch the next available ID via AJAX
            fetch('egg_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=getNextId'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('ceggid').value = data.nextEggId;
                    document.querySelector('.form-text').textContent = 'Next available ID: ' + data.nextEggId;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        

        
        function deleteEgg(eggId) {
            if (confirm(`Are you sure you want to delete egg '${eggId}'? This action cannot be undone.`)) {
                fetch('egg_data.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'action=delete&ceggid=' + encodeURIComponent(eggId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the row from the table dynamically
                        const row = document.querySelector(`tr[data-egg-id="${eggId}"]`);
                        if (row) {
                            row.remove();
                            updateStatistics();
                            showMessage('Egg deleted successfully!', 'success');
                        }
                    } else {
                        showMessage('Error deleting egg: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error deleting egg. Please try again.', 'danger');
                });
            }
        }
        
        function updateEgg(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const data = {
                action: 'update',
                ceggid: formData.get('ceggid'),
                nweight: formData.get('nweight'),
                nlength: formData.get('nlength'),
                nwidth: formData.get('nwidth'),
                ceggcondition: formData.get('ceggcondition'),
                cincubatorid: formData.get('cincubatorid'),
                cbreedingid: formData.get('cbreedingid')
            };
            
            fetch('egg_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Update the row in the table dynamically
                    updateTableRow(data.ceggid, data);
                    updateStatistics();
                    showMessage('Egg updated successfully!', 'success');
                    $('#editEggModal').modal('hide');
                } else {
                    showMessage('Error updating egg: ' + result.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error updating egg. Please try again.', 'danger');
            });
            
            return false;
        }
        
        function addEgg(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const data = {
                action: 'add',
                ceggid: formData.get('ceggid'),
                nweight: formData.get('nweight'),
                nlength: formData.get('nlength'),
                nwidth: formData.get('nwidth'),
                ceggcondition: formData.get('ceggcondition'),
                cincubatorid: formData.get('cincubatorid'),
                cbreedingid: formData.get('cbreedingid')
            };
            
            fetch('egg_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Add new row to the table dynamically
                    addTableRow(data);
                    updateStatistics();
                    showMessage('Egg added successfully!', 'success');
                    $('#addEggModal').modal('hide');
                    clearAddForm();
                } else {
                    showMessage('Error adding egg: ' + result.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error adding egg. Please try again.', 'danger');
            });
            
            return false;
        }
        
        function updateTableRow(eggId, data) {
            const row = document.querySelector(`tr[data-egg-id="${eggId}"]`);
            if (row) {
                row.cells[1].textContent = data.nweight || '-';
                row.cells[2].textContent = data.nlength || '-';
                row.cells[3].textContent = data.nwidth || '-';
                row.cells[4].innerHTML = data.ceggcondition ? 
                    `<span class="badge badge-${data.ceggcondition === 'Normal' ? 'success' : 'warning'}">${data.ceggcondition}</span>` : 
                    '-';
                row.cells[5].textContent = data.cincubatorid || '-';
                row.cells[6].textContent = data.cbreedingid || '-';
            }
        }
        
        function addTableRow(data) {
            const tbody = document.querySelector('table tbody');
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-egg-id', data.ceggid);
            
            const conditionBadge = data.ceggcondition ? 
                `<span class="badge badge-${data.ceggcondition === 'Normal' ? 'success' : 'warning'}">${data.ceggcondition}</span>` : 
                '-';
            
            newRow.innerHTML = `
                <td>${data.ceggid}</td>
                <td>${data.nweight || '-'}</td>
                <td>${data.nlength || '-'}</td>
                <td>${data.nwidth || '-'}</td>
                <td>${conditionBadge}</td>
                <td>${data.cincubatorid || '-'}</td>
                <td>${data.cbreedingid || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-primary mr-2" data-toggle="modal" data-target="#editEggModal" 
                            onclick="populateEditForm('${data.ceggid}', '${data.nweight || ''}', '${data.nlength || ''}', '${data.nwidth || ''}', '${data.ceggcondition || ''}', '${data.cincubatorid || ''}', '${data.cbreedingid || ''}')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteEgg('${data.ceggid}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(newRow);
        }
        
        function updateStatistics() {
            const rows = document.querySelectorAll('table tbody tr');
            let totalEggs = rows.length;
            let healthyEggs = 0;
            let problemEggs = 0;
            let totalWeight = 0;
            let weightCount = 0;
            
            rows.forEach(row => {
                const conditionCell = row.cells[4];
                const condition = conditionCell.textContent.trim();
                const weight = parseInt(row.cells[1].textContent);
                
                if (condition === 'Normal') {
                    healthyEggs++;
                } else if (condition !== '-') {
                    problemEggs++;
                }
                
                if (weight && !isNaN(weight)) {
                    totalWeight += weight;
                    weightCount++;
                }
            });
            
            const avgWeight = weightCount > 0 ? Math.round(totalWeight / weightCount * 10) / 10 : 0;
            
            // Update statistics cards
            const totalCard = document.querySelector('.card.border-left-primary .h5');
            const healthyCard = document.querySelector('.card.border-left-success .h5');
            const avgWeightCard = document.querySelector('.card.border-left-info .h5');
            const problemCard = document.querySelector('.card.border-left-warning .h5');
            
            if (totalCard) totalCard.textContent = totalEggs;
            if (healthyCard) healthyCard.textContent = healthyEggs;
            if (avgWeightCard) avgWeightCard.textContent = avgWeight + 'g';
            if (problemCard) problemCard.textContent = problemEggs;
        }
        
        function showMessage(message, type) {
            // Remove any existing messages first
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());
            
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            `;
            
            const container = document.querySelector('.container-fluid');
            const pageHeading = container.querySelector('.d-sm-flex');
            if (container && pageHeading) {
                container.insertBefore(alertDiv, pageHeading.nextSibling);
            }
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
        
        function clearAddForm() {
            document.getElementById('addEggForm').reset();
            // Reset the egg ID to the next available one
            generateNextId();
        }
        
        // Form validation and modal handling
        document.addEventListener('DOMContentLoaded', function() {
            // Handle modal closing
            $('#addEggModal').on('hidden.bs.modal', function () {
                clearAddForm();
            });
            
            $('#editEggModal').on('hidden.bs.modal', function () {
                // Reset edit form
                document.getElementById('editEggForm').reset();
            });
            
            // Form validation
            const addForm = document.querySelector('#addEggForm');
            if (addForm) {
                addForm.addEventListener('submit', function(e) {
                    const eggId = document.getElementById('ceggid').value.trim();
                    if (!eggId.match(/^E\d+$/)) {
                        e.preventDefault();
                        showMessage('Egg ID must start with "E" followed by numbers (e.g., E001, E002)', 'danger');
                        return false;
                    }
                });
            }
        });
    </script>
</body>
</html>
