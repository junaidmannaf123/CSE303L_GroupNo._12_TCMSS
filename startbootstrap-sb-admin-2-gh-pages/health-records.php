<?php
require_once __DIR__ . '/config/database.php'; 

// Helpers
function renderVaccinationBadge($text) {
    if ($text === null || $text === '') return '';
    $t = strtolower((string)$text);
    if (strpos($t, 'overdue') !== false) return '<span class="badge badge-danger">' . htmlspecialchars($text) . '</span>';
    if (strpos($t, 'pending') !== false || strpos($t, 'due') !== false) return '<span class="badge badge-warning">' . htmlspecialchars($text) . '</span>';
    return '<span class="badge badge-success">' . htmlspecialchars($text) . '</span>';
}

function fetchMedicalRecords(PDO $pdo, array $filters = []) {
    $where = [];
    $params = [];
    if (!empty($filters['tortoiseId'])) {
        $where[] = 'mr.ctortoiseid = :tortoiseId';
        $params[':tortoiseId'] = $filters['tortoiseId'];
    }
    if (!empty($filters['type'])) {
        $where[] = 'mr.ctype = :type';
        $params[':type'] = $filters['type'];
    }
    if (!empty($filters['dateFrom'])) {
        $where[] = 'mr.ddate >= :dateFrom';
        $params[':dateFrom'] = $filters['dateFrom'];
    }
    if (!empty($filters['dateTo'])) {
        $where[] = 'mr.ddate <= :dateTo';
        $params[':dateTo'] = $filters['dateTo'];
    }
    if (!empty($filters['notes'])) {
        $where[] = '(mr.cdiagnosis LIKE :notes OR mr.ctreatment LIKE :notes)';
        $params[':notes'] = '%' . $filters['notes'] . '%';
    }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    $sql = "
        SELECT 
            mr.crecordid,
            mr.drecordingdate,
            mr.cdiagnosis,
            mr.ctreatment,
            mr.ctype,
            mr.ddate,
            mr.cvaccinationstatus,
            mr.dcheckdate,
            mr.dchecktime,
            mr.cstaffid,
            mr.ctortoiseid
        FROM tblmedicalrecords mr
        $whereSql
        ORDER BY mr.drecordingdate DESC, mr.crecordid ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// CRUD Handlers
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    try {
        if ($action === 'create') {
            $drecordingdate = isset($_POST['drecordingdate']) ? $_POST['drecordingdate'] : '';
            $ctype = isset($_POST['ctype']) ? $_POST['ctype'] : '';
            $ctortoiseid = isset($_POST['ctortoiseid']) ? $_POST['ctortoiseid'] : '';
            $cstaffid = isset($_POST['cstaffid']) ? $_POST['cstaffid'] : '';
            if (!$drecordingdate || !$ctype || !$ctortoiseid || !$cstaffid) {
                throw new Exception('Please fill in required fields.');
            }
            // Ensure tortoise exists (best effort)
            $stmt = $pdo->prepare('SELECT ctortoiseid FROM tbltortoise WHERE ctortoiseid = :id');
            $stmt->execute([':id' => $ctortoiseid]);
            if (!$stmt->fetch()) {
                throw new Exception('Invalid tortoise ID.');
            }
            // Generate next MR id
            $row = $pdo->query("SELECT MAX(CAST(SUBSTRING(crecordid,3) AS UNSIGNED)) AS max_num FROM tblmedicalrecords WHERE crecordid REGEXP '^MR[0-9]+'")->fetch();
            $nextNum = (int)($row && isset($row['max_num']) ? $row['max_num'] : 0) + 1;
            $newId = 'MR' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);
            $sql = 'INSERT INTO tblmedicalrecords (
                crecordid, drecordingdate, cdiagnosis, ctreatment, ctype, ddate,
                cvaccinationstatus, dcheckdate, dchecktime, cstaffid, ctortoiseid
            ) VALUES (
                :crecordid, :drecordingdate, :cdiagnosis, :ctreatment, :ctype, :ddate,
                :cvaccinationstatus, :dcheckdate, :dchecktime, :cstaffid, :ctortoiseid
            )';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':crecordid' => $newId,
                ':drecordingdate' => $drecordingdate,
                ':cdiagnosis' => isset($_POST['cdiagnosis']) && $_POST['cdiagnosis'] !== '' ? $_POST['cdiagnosis'] : null,
                ':ctreatment' => isset($_POST['ctreatment']) && $_POST['ctreatment'] !== '' ? $_POST['ctreatment'] : null,
                ':ctype' => $ctype,
                ':ddate' => isset($_POST['ddate']) && $_POST['ddate'] !== '' ? $_POST['ddate'] : $drecordingdate,
                ':cvaccinationstatus' => isset($_POST['cvaccinationstatus']) && $_POST['cvaccinationstatus'] !== '' ? $_POST['cvaccinationstatus'] : null,
                ':dcheckdate' => isset($_POST['dcheckdate']) && $_POST['dcheckdate'] !== '' ? $_POST['dcheckdate'] : null,
                ':dchecktime' => isset($_POST['dchecktime']) && $_POST['dchecktime'] !== '' ? $_POST['dchecktime'] : null,
                ':cstaffid' => $cstaffid,
                ':ctortoiseid' => $ctortoiseid
            ]);
            $success = 'Medical record added: ' . htmlspecialchars($newId);
        } elseif ($action === 'update') {
            $crecordid = isset($_POST['crecordid']) ? $_POST['crecordid'] : '';
            if (!$crecordid) throw new Exception('Missing record id');
            $stmt = $pdo->prepare('SELECT crecordid FROM tblmedicalrecords WHERE crecordid = :id');
            $stmt->execute([':id' => $crecordid]);
            if (!$stmt->fetch()) throw new Exception('Record not found');
            $fields = [
                'drecordingdate', 'cdiagnosis', 'ctreatment', 'ctype', 'ddate',
                'cvaccinationstatus', 'dcheckdate', 'dchecktime', 'cstaffid', 'ctortoiseid'
            ];
            $sets = [];
            $params = [':id' => $crecordid];
            foreach ($fields as $f) {
                if (array_key_exists($f, $_POST)) {
                    $sets[] = "$f = :$f";
                    $params[":$f"] = ($_POST[$f] === '' ? null : $_POST[$f]);
                }
            }
            if ($sets) {
                $sql = 'UPDATE tblmedicalrecords SET ' . implode(', ', $sets) . ' WHERE crecordid = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $success = 'Medical record updated: ' . htmlspecialchars($crecordid);
            } else {
                $success = 'Nothing to update';
            }
        } elseif ($action === 'delete') {
            $crecordid = isset($_POST['crecordid']) ? $_POST['crecordid'] : '';
            if (!$crecordid) throw new Exception('Missing record id');
            $stmt = $pdo->prepare('DELETE FROM tblmedicalrecords WHERE crecordid = :id');
            $stmt->execute([':id' => $crecordid]);
            $success = 'Medical record deleted: ' . htmlspecialchars($crecordid);
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// Prefill for edit form
$editId = isset($_GET['edit']) ? $_GET['edit'] : '';
$editRecord = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM tblmedicalrecords WHERE crecordid = :id');
    $stmt->execute([':id' => $editId]);
    $editRecord = $stmt->fetch();
}

// Filters from GET
$filters = [
    'tortoiseId' => isset($_GET['tortoiseId']) ? $_GET['tortoiseId'] : '',
    'type' => isset($_GET['type']) ? $_GET['type'] : '',
    'dateFrom' => isset($_GET['dateFrom']) ? $_GET['dateFrom'] : '',
    'dateTo' => isset($_GET['dateTo']) ? $_GET['dateTo'] : '',
    'notes' => isset($_GET['notes']) ? $_GET['notes'] : ''
];
$records = fetchMedicalRecords($pdo, $filters);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Tortoise Conservation Health Records">
    <meta name="author" content="">
    <title>Tortoise Conservation - Health Records</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
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
            <li class="nav-item">
                <a class="nav-link" href="vetdashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Vet Tools</div>
            <li class="nav-item active">
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
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <h4 class="ml-3 mt-2 text-success font-weight-bold">Health Records</h4>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Farhana Rahman</span>
                                <i class="fas fa-user-nurse fa-2x text-success img-profile rounded-circle"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Profile</a>
                                <a class="dropdown-item" href="#"><i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Settings</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Logout</a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Health Records</h1>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-<?php echo $editRecord ? 'edit' : 'plus-circle'; ?> mr-2"></i><?php echo $editRecord ? 'Edit Health Record' : 'Add New Health Record'; ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="action" value="<?php echo $editRecord ? 'update' : 'create'; ?>">
                                <?php if ($editRecord): ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Record ID</label>
                                            <input type="text" class="form-control" name="crecordid" value="<?php echo htmlspecialchars($editRecord['crecordid']); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Recording Date *</label>
                                            <input type="date" class="form-control" name="drecordingdate" value="<?php echo htmlspecialchars($editRecord['drecordingdate'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Tortoise ID *</label>
                                            <input type="text" class="form-control" name="ctortoiseid" value="<?php echo htmlspecialchars($editRecord['ctortoiseid'] ?? ''); ?>" required placeholder="e.g. 001">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Staff ID *</label>
                                            <input type="text" class="form-control" name="cstaffid" value="<?php echo htmlspecialchars($editRecord['cstaffid'] ?? ''); ?>" required placeholder="e.g. SM004">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Diagnosis</label>
                                            <input type="text" class="form-control" name="cdiagnosis" value="<?php echo htmlspecialchars($editRecord['cdiagnosis'] ?? ''); ?>" placeholder="Enter diagnosis">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Treatment</label>
                                            <input type="text" class="form-control" name="ctreatment" value="<?php echo htmlspecialchars($editRecord['ctreatment'] ?? ''); ?>" placeholder="Enter treatment">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Type *</label>
                                            <select class="form-control" name="ctype" required>
                                                <?php
                                                $types = ['Illness','Injury','Checkup','Emergency','Monitoring','Infection','Surgery','Recovery','Nutrition'];
                                                $selType = $editRecord['ctype'] ?? '';
                                                echo '<option value="">Select type</option>';
                                                foreach ($types as $t) {
                                                    $sel = ($selType === $t) ? ' selected' : '';
                                                    echo '<option value="' . htmlspecialchars($t) . '"' . $sel . '>' . htmlspecialchars($t) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Date</label>
                                            <input type="date" class="form-control" name="ddate" value="<?php echo htmlspecialchars($editRecord['ddate'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Vaccination Status</label>
                                            <select class="form-control" name="cvaccinationstatus">
                                                <?php
                                                $vacc = ['', 'Up-to-date', 'Pending', 'Overdue'];
                                                $selV = $editRecord['cvaccinationstatus'] ?? '';
                                                foreach ($vacc as $v) {
                                                    $sel = ($selV === $v) ? ' selected' : '';
                                                    $label = $v === '' ? 'Select' : $v;
                                                    echo '<option value="' . htmlspecialchars($v) . '"' . $sel . '>' . htmlspecialchars($label) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Check Date</label>
                                            <input type="date" class="form-control" name="dcheckdate" value="<?php echo htmlspecialchars($editRecord['dcheckdate'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Check Time</label>
                                            <input type="time" class="form-control" name="dchecktime" value="<?php echo htmlspecialchars($editRecord['dchecktime'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <button class="btn btn-success btn-sm mr-2" type="submit"><i class="fas fa-save mr-1"></i><?php echo $editRecord ? 'Update Record' : 'Save Record'; ?></button>
                                    <?php if ($editRecord): ?>
                                    <a class="btn btn-secondary btn-sm" href="health-records.php">Cancel</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- Filter Section -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-filter mr-2"></i>Filter Records
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="filterForm" method="get">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filterTortoiseId" class="font-weight-bold text-gray-800">Tortoise ID</label>
                                            <input type="text" class="form-control" id="filterTortoiseId" name="tortoiseId" value="<?php echo htmlspecialchars($filters['tortoiseId']); ?>" placeholder="Enter tortoise ID">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filterType" class="font-weight-bold text-gray-800">Type</label>
                                            <select class="form-control" id="filterType" name="type">
                                                <?php
                                                $ftypes = ['' => 'All Types','Illness'=>'Illness','Injury'=>'Injury','Checkup'=>'Checkup','Emergency'=>'Emergency','Monitoring'=>'Monitoring','Infection'=>'Infection','Surgery'=>'Surgery','Recovery'=>'Recovery','Nutrition'=>'Nutrition'];
                                                foreach ($ftypes as $val => $label) {
                                                    $sel = ($filters['type'] === $val) ? ' selected' : '';
                                                    echo '<option value="' . htmlspecialchars($val) . '"' . $sel . '>' . htmlspecialchars($label) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filterDateFrom" class="font-weight-bold text-gray-800">Date From</label>
                                            <input type="date" class="form-control" id="filterDateFrom" name="dateFrom" value="<?php echo htmlspecialchars($filters['dateFrom']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filterDateTo" class="font-weight-bold text-gray-800">Date To</label>
                                            <input type="date" class="form-control" id="filterDateTo" name="dateTo" value="<?php echo htmlspecialchars($filters['dateTo']); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="filterNotes" class="font-weight-bold text-gray-800">Search in Notes</label>
                                            <input type="text" class="form-control" id="filterNotes" name="notes" value="<?php echo htmlspecialchars($filters['notes']); ?>" placeholder="Search in notes...">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Actions</label>
                                            <div class="d-flex">
                                                <button type="submit" class="btn btn-success btn-sm mr-2">
                                                    <i class="fas fa-search mr-1"></i>Apply Filter
                                                </button>
                                                <a href="health-records.php" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-times mr-1"></i>Clear Filter
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- End Filter Section -->
                    
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-success">All Health Records</h6>
                            <div class="text-muted">
                                <span id="recordCount">Showing <?php echo count($records); ?> records</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Record ID</th>
                                            <th>Recording Date</th>
                                            <th>Diagnosis</th>
                                            <th>Treatment</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Vaccination Status</th>
                                            <th>Check Date</th>
                                            <th>Check Time</th>
                                            <th>Staff ID</th>
                                            <th>Tortoise ID</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="healthRecordsTableBody">
<?php foreach ($records as $r): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($r['crecordid'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($r['drecordingdate'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($r['cdiagnosis'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($r['ctreatment'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($r['ctype'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($r['ddate'] ?? ''); ?></td>
                                            <td><?php echo renderVaccinationBadge($r['cvaccinationstatus'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($r['dcheckdate'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($r['dchecktime'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($r['cstaffid'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($r['ctortoiseid'] ?? ''); ?></td>
                                            <td>
                                                <a class="btn btn-sm btn-primary mr-1" href="health-records.php?edit=<?php echo urlencode($r['crecordid']); ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="post" style="display:inline;" onsubmit="return confirm('Delete record <?php echo htmlspecialchars($r['crecordid']); ?>?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="crecordid" value="<?php echo htmlspecialchars($r['crecordid']); ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
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
</body>
</html> 