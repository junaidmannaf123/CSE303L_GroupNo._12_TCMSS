<?php
require_once 'config/database.php';


try {
    $stmt = $pdo->prepare("
        SELECT 
            cfeedingid,
            DATE_FORMAT(ddate, '%Y-%m-%d') as ddate,
            TIME_FORMAT(dtime, '%H:%i') as dtime,
            cdietnotes,
            cstaffid,
            cenclosureid
        FROM tblfeedingschedule 
        ORDER BY ddate DESC, dtime DESC
    ");
    
    $stmt->execute();
    $feedingSchedules = $stmt->fetchAll();
} catch(PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    $feedingSchedules = [];
}


$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    $feedingID = $_POST['feedingID'];
                    $date = $_POST['date'];
                    $time = $_POST['time'];
                    $dietNotes = $_POST['dietNotes'] ?? '';
                    $staffID = $_POST['staffID'];
                    $enclosureID = $_POST['enclosureID'];

                    
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM tblfeedingschedule WHERE cfeedingid = ?");
                    $checkStmt->execute([$feedingID]);
                    
                    if ($checkStmt->fetchColumn() > 0) {
                        throw new Exception('Feeding ID already exists');
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO tblfeedingschedule 
                        (cfeedingid, ddate, dtime, cdietnotes, cstaffid, cenclosureid) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([$feedingID, $date, $time, $dietNotes, $staffID, $enclosureID]);
                    
                    $message = "Feeding schedule added successfully!";
                    $message_type = "success";
                    
                    
                    $stmt = $pdo->prepare("
                        SELECT 
                            cfeedingid,
                            DATE_FORMAT(ddate, '%Y-%m-%d') as ddate,
                            TIME_FORMAT(dtime, '%H:%i') as dtime,
                            cdietnotes,
                            cstaffid,
                            cenclosureid
                        FROM tblfeedingschedule 
                        ORDER BY ddate DESC, dtime DESC
                    ");
                    $stmt->execute();
                    $feedingSchedules = $stmt->fetchAll();
                    
                } catch(Exception $e) {
                    $message = "Error: " . $e->getMessage();
                    $message_type = "danger";
                }
                break;

            case 'update':
                try {
                    $feedingID = $_POST['feedingID'];
                    $date = $_POST['date'];
                    $time = $_POST['time'];
                    $dietNotes = $_POST['dietNotes'] ?? '';
                    $staffID = $_POST['staffID'];
                    $enclosureID = $_POST['enclosureID'];

                    $stmt = $pdo->prepare("
                        UPDATE tblfeedingschedule 
                        SET ddate = ?, dtime = ?, cdietnotes = ?, cstaffid = ?, cenclosureid = ?
                        WHERE cfeedingid = ?
                    ");
                    
                    $stmt->execute([$date, $time, $dietNotes, $staffID, $enclosureID, $feedingID]);
                    
                    $message = "Feeding schedule updated successfully!";
                    $message_type = "success";
                    
                    
                    $stmt = $pdo->prepare("
                        SELECT 
                            cfeedingid,
                            DATE_FORMAT(ddate, '%Y-%m-%d') as ddate,
                            TIME_FORMAT(dtime, '%H:%i') as dtime,
                            cdietnotes,
                            cstaffid,
                            cenclosureid
                        FROM tblfeedingschedule 
                        ORDER BY ddate DESC, dtime DESC
                    ");
                    $stmt->execute();
                    $feedingSchedules = $stmt->fetchAll();
                    
                } catch(Exception $e) {
                    $message = "Error: " . $e->getMessage();
                    $message_type = "danger";
                }
                break;

            case 'delete':
                try {
                    $feedingID = $_POST['feedingID'];
                    
                    $stmt = $pdo->prepare("DELETE FROM tblfeedingschedule WHERE cfeedingid = ?");
                    $stmt->execute([$feedingID]);
                    
                    $message = "Feeding schedule deleted successfully!";
                    $message_type = "success";
                    
                    // Refresh the data
                    $stmt = $pdo->prepare("
                        SELECT 
                            cfeedingid,
                            DATE_FORMAT(ddate, '%Y-%m-%d') as ddate,
                            TIME_FORMAT(dtime, '%H:%i') as dtime,
                            cdietnotes,
                            cstaffid,
                            cenclosureid
                        FROM tblfeedingschedule 
                        ORDER BY ddate DESC, dtime DESC
                    ");
                    $stmt->execute();
                    $feedingSchedules = $stmt->fetchAll();
                    
                } catch(Exception $e) {
                    $message = "Error: " . $e->getMessage();
                    $message_type = "danger";
                }
                break;
        }
    }
}


function generateFeedingID() {
    return 'FD' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tortoise Feeding Schedule</title>
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" />
  <link href="css/sb-admin-2.min.css" rel="stylesheet" />
</head>

<body id="page-top">
  <div id="wrapper">

    <ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="caretaker_dashboard.php">
        <div class="sidebar-brand-text mx-3">🐢 TCCMS</div>
      </a>

      <hr class="sidebar-divider my-0" />
      <li class="nav-item"><a class="nav-link" href="caretaker_dashboard.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a></li>
      <hr class="sidebar-divider" />
      <li class="nav-item"><a class="nav-link" href="caretaker_tasks.php"><i class="fas fa-tasks"></i><span>Tasks</span></a></li>
      <li class="nav-item active"><a class="nav-link" href="feeding.php"><i class="fas fa-seedling"></i><span>Feeding Schedule</span></a></li>
      <hr class="sidebar-divider d-none d-md-block" />
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">

        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
          <h1 class="h4 text-gray-800 mb-0">Feeding Schedule</h1>
        </nav>

        <div class="container-fluid">

          <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
              <?php echo $message; ?>
              <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
              </button>
            </div>
          <?php endif; ?>

          <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?php echo $error_message; ?>
              <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
              </button>
            </div>
          <?php endif; ?>

          <div class="mb-3 text-right">
            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addFeedingModal">
              <i class="fas fa-plus-circle"></i> Add Feeding
            </button>
          </div>

          <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success">
              <h6 class="m-0 font-weight-bold text-white">Feeding Schedule Table</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-hover" id="feedingTable">
                  <thead class="bg-light text-success text-center">
                    <tr>
                      <th>Feeding ID</th>
                      <th>Date</th>
                      <th>Time</th>
                      <th>Diet Notes</th>
                      <th>Staff ID</th>
                      <th>Enclosure ID</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($feedingSchedules)): ?>
                      <?php foreach ($feedingSchedules as $schedule): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($schedule['cfeedingid']); ?></td>
                          <td><?php echo htmlspecialchars($schedule['ddate']); ?></td>
                          <td><?php echo htmlspecialchars($schedule['dtime']); ?></td>
                          <td><?php echo htmlspecialchars($schedule['cdietnotes'] ?? ''); ?></td>
                          <td><?php echo htmlspecialchars($schedule['cstaffid']); ?></td>
                          <td><?php echo htmlspecialchars($schedule['cenclosureid']); ?></td>
                          <td class="text-center">
                            <button class="btn btn-sm btn-warning edit-btn" 
                                    data-id="<?php echo htmlspecialchars($schedule['cfeedingid']); ?>"
                                    data-date="<?php echo htmlspecialchars($schedule['ddate']); ?>"
                                    data-time="<?php echo htmlspecialchars($schedule['dtime']); ?>"
                                    data-diet="<?php echo htmlspecialchars($schedule['cdietnotes'] ?? ''); ?>"
                                    data-staff="<?php echo htmlspecialchars($schedule['cstaffid']); ?>"
                                    data-enclosure="<?php echo htmlspecialchars($schedule['cenclosureid']); ?>">
                              <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this feeding schedule?');">
                              <input type="hidden" name="action" value="delete">
                              <input type="hidden" name="feedingID" value="<?php echo htmlspecialchars($schedule['cfeedingid']); ?>">
                              <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                              </button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="7" class="text-center">No feeding schedules found.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
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

  <div class="modal fade" id="addFeedingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 id="modalTitle">Add New Feeding</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <form id="feedingForm" method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="feedingID" id="formFeedingID" value="<?php echo generateFeedingID(); ?>">
            
            <div class="form-group">
              <label>Date</label>
              <input type="date" class="form-control" name="date" id="feedingDate" required>
            </div>
            <div class="form-group">
              <label>Time</label>
              <input type="time" class="form-control" name="time" id="feedingTime" required>
            </div>
            <div class="form-group">
              <label>Diet Notes</label>
              <input type="text" class="form-control" name="dietNotes" id="dietNotes">
            </div>
            <div class="form-group">
              <label>Staff ID</label>
              <input type="text" class="form-control" name="staffID" id="staffID" required>
            </div>
            <div class="form-group">
              <label>Enclosure ID</label>
              <input type="text" class="form-control" name="enclosureID" id="enclosureID" required>
            </div>
            
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success" id="submitBtn">Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    
    document.addEventListener('DOMContentLoaded', function() {
      const editButtons = document.querySelectorAll('.edit-btn');
      
      editButtons.forEach(button => {
        button.addEventListener('click', function() {
          const feedingID = this.getAttribute('data-id');
          const date = this.getAttribute('data-date');
          const time = this.getAttribute('data-time');
          const diet = this.getAttribute('data-diet');
          const staff = this.getAttribute('data-staff');
          const enclosure = this.getAttribute('data-enclosure');
          
          
          document.getElementById('formAction').value = 'update';
          document.getElementById('formFeedingID').value = feedingID;
          document.getElementById('feedingDate').value = date;
          document.getElementById('feedingTime').value = time;
          document.getElementById('dietNotes').value = diet;
          document.getElementById('staffID').value = staff;
          document.getElementById('enclosureID').value = enclosure;
          
          
          document.getElementById('modalTitle').textContent = 'Edit Feeding Schedule';
          document.getElementById('submitBtn').textContent = 'Update';
          
          $('#addFeedingModal').modal('show');
        });
      });
    });

    
    $('#addFeedingModal').on('hidden.bs.modal', function () {
      document.getElementById('feedingForm').reset();
      document.getElementById('formAction').value = 'add';
      document.getElementById('formFeedingID').value = '<?php echo generateFeedingID(); ?>';
      document.getElementById('modalTitle').textContent = 'Add New Feeding';
      document.getElementById('submitBtn').textContent = 'Save';
    });

    
    setTimeout(function() {
      const alerts = document.querySelectorAll('.alert');
      alerts.forEach(alert => {
        if (alert.classList.contains('alert-dismissible')) {
          alert.remove();
        }
      });
    }, 5000);
  </script>

</body>
</html>
