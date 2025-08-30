<?php
require_once 'config/database.php';

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_task':
                try {
                    $taskName = $_POST['taskName'];
                    $dueTime = $_POST['dueTime'];
                    $taskStatus = $_POST['taskStatus'] ?? 'Pending';
                    $staffID = $_POST['staffID'] ?? 'SM002';

                    // Generate unique task ID
                    $taskID = 'TK' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

                    // Insert new task
                    $stmt = $pdo->prepare("
                        INSERT INTO tbltasks 
                        (ctaskid, ctaskname, dduetime, cstatus, cstaffid, dcreatedate) 
                        VALUES (?, ?, ?, ?, ?, CURDATE())
                    ");
                    
                    $stmt->execute([$taskID, $taskName, $dueTime, $taskStatus, $staffID]);
                    
                    $message = "Task added successfully!";
                    $message_type = "success";
                    
                } catch(Exception $e) {
                    $message = "Error: " . $e->getMessage();
                    $message_type = "danger";
                }
                break;

            case 'update_status':
                try {
                    $taskID = $_POST['taskID'];
                    $newStatus = $_POST['status'];
                    
                    $stmt = $pdo->prepare("UPDATE tbltasks SET cstatus = ? WHERE ctaskid = ?");
                    $stmt->execute([$newStatus, $taskID]);
                    
                    $message = "Task status updated successfully!";
                    $message_type = "success";
                    
                } catch(Exception $e) {
                    $message = "Error: " . $e->getMessage();
                    $message_type = "danger";
                }
                break;

            case 'delete_task':
                try {
                    $taskID = $_POST['taskID'];
                    
                    $stmt = $pdo->prepare("DELETE FROM tbltasks WHERE ctaskid = ?");
                    $stmt->execute([$taskID]);
                    
                    $message = "Task deleted successfully!";
                    $message_type = "success";
                    
                } catch(Exception $e) {
                    $message = "Error: " . $e->getMessage();
                    $message_type = "danger";
                }
                break;
        }
    }
}

// Fetch tasks from database
try {
    $stmt = $pdo->prepare("
        SELECT 
            ctaskid,
            ctaskname,
            TIME_FORMAT(dduetime, '%H:%i') as due_time,
            cstatus,
            cstaffid,
            DATE_FORMAT(dcreatedate, '%Y-%m-%d') as created_date
        FROM tbltasks 
        ORDER BY dduetime ASC
    ");
    
    $stmt->execute();
    $tasks = $stmt->fetchAll();
} catch(PDOException $e) {
    // If table doesn't exist, create sample data
    $tasks = [
        [
            'ctaskid' => 'TK001',
            'ctaskname' => 'Clean enclosure',
            'due_time' => '08:00',
            'cstatus' => 'Pending',
            'cstaffid' => 'SM002',
            'created_date' => date('Y-m-d')
        ],
        [
            'ctaskid' => 'TK002',
            'ctaskname' => 'Clean water bowls',
            'due_time' => '09:00',
            'cstatus' => 'Pending',
            'cstaffid' => 'SM002',
            'created_date' => date('Y-m-d')
        ],
        [
            'ctaskid' => 'TK003',
            'ctaskname' => 'Get rid of leftover foods',
            'due_time' => '10:00',
            'cstatus' => 'Pending',
            'cstaffid' => 'SM002',
            'created_date' => date('Y-m-d')
        ],
        [
            'ctaskid' => 'TK004',
            'ctaskname' => 'Clean tortoise shell gently',
            'due_time' => '10:30',
            'cstatus' => 'Pending',
            'cstaffid' => 'SM002',
            'created_date' => date('Y-m-d')
        ],
        [
            'ctaskid' => 'TK005',
            'ctaskname' => 'Feed tortoises',
            'due_time' => '11:30',
            'cstatus' => 'Pending',
            'cstaffid' => 'SM002',
            'created_date' => date('Y-m-d')
        ],
        [
            'ctaskid' => 'TK006',
            'ctaskname' => 'Refill fresh water bowls if required',
            'due_time' => '12:30',
            'cstatus' => 'Pending',
            'cstaffid' => 'SM002',
            'created_date' => date('Y-m-d')
        ],
        [
            'ctaskid' => 'TK007',
            'ctaskname' => 'Record health',
            'due_time' => '13:00',
            'cstatus' => 'Pending',
            'cstaffid' => 'SM002',
            'created_date' => date('Y-m-d')
        ],
        [
            'ctaskid' => 'TK008',
            'ctaskname' => 'Check basking lamp',
            'due_time' => '14:00',
            'cstatus' => 'Pending',
            'cstaffid' => 'SM002',
            'created_date' => date('Y-m-d')
        ],
        [
            'ctaskid' => 'TK009',
            'ctaskname' => 'Report malfunctioning lights',
            'due_time' => '14:30',
            'cstatus' => 'Pending',
            'cstaffid' => 'SM002',
            'created_date' => date('Y-m-d')
        ],
        [
            'ctaskid' => 'TK010',
            'ctaskname' => 'Clean enclosure before leaving',
            'due_time' => '16:00',
            'cstatus' => 'Pending',
            'cstaffid' => 'SM002',
            'created_date' => date('Y-m-d')
        ]
    ];
}

// Function to generate unique task ID
function generateTaskID() {
    return 'TK' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
}

// Function to format time for display
function formatTimeForDisplay($time) {
    $hour = (int)substr($time, 0, 2);
    $minute = substr($time, 3, 2);
    $ampm = $hour >= 12 ? 'PM' : 'AM';
    $hour = $hour % 12 || 12;
    return sprintf('%d:%s %s', $hour, $minute, $ampm);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Caretaker Tasks - TCCMS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
      <li class="nav-item">
        <a class="nav-link" href="caretaker_dashboard.php">
          <i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span>
        </a>
      </li>
      <hr class="sidebar-divider">
      <li class="nav-item active">
        <a class="nav-link" href="caretaker_tasks.php">
          <i class="fas fa-tasks"></i><span>Tasks</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="feeding.php">
          <i class="fas fa-seedling"></i><span>Feeding Schedule</span>
        </a>
      </li>
      <hr class="sidebar-divider d-none d-md-block">
    </ul>
    

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        
        
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
          <h1 class="h4 text-gray-800">Daily Tasks</h1>
          <ul class="navbar-nav ml-auto">
            <div class="topbar-divider d-none d-sm-block"></div>
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Profile</span>
                <i class="fas fa-user fa-2x text-success img-profile rounded-circle"></i>
              </a>
            </li>
          </ul>
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
         
          <button class="btn btn-success mb-3" id="showTaskForm">
            <i class="fas fa-plus-circle"></i> Add Task
          </button>
          
          <div id="taskForm" class="card shadow mb-4" style="display: none;">
            <div class="card-header py-3 bg-success text-white">
              <h6 class="m-0 font-weight-bold">New Task</h6>
            </div>
            <div class="card-body">
              <form id="newTaskForm" method="POST">
                <input type="hidden" name="action" value="add_task">
                <div class="form-group">
                  <label for="taskName">Task</label>
                  <input type="text" class="form-control" name="taskName" id="taskName" required>
                </div>
                <div class="form-group">
                  <label for="dueTime">Due Time</label>
                  <input type="time" class="form-control" name="dueTime" id="dueTime" required>
                </div>
                <div class="form-group">
                  <label for="taskStatus">Status</label>
                  <select class="form-control" name="taskStatus" id="taskStatus">
                    <option value="Pending">Pending</option>
                    <option value="Completed">Completed</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="staffID">Staff ID</label>
                  <input type="text" class="form-control" name="staffID" id="staffID" value="SM002" required>
                </div>
                <button type="submit" class="btn btn-primary">Confirm</button>
              </form>
            </div>
          </div>

          
          <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success">
              <h6 class="m-0 font-weight-bold text-white">Task List</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="taskTable">
                  <thead class="bg-light text-success">
                    <tr>
                      <th>Task ID</th>
                      <th>Task</th>
                      <th>Due Time</th>
                      <th>Status</th>
                      <th>Staff</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($tasks)): ?>
                      <?php foreach ($tasks as $task): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($task['ctaskid']); ?></td>
                          <td><?php echo htmlspecialchars($task['ctaskname']); ?></td>
                          <td><?php echo formatTimeForDisplay($task['due_time']); ?></td>
                          <td class="status">
                            <span class="badge badge-<?php echo $task['cstatus'] === 'Completed' ? 'success' : 'warning'; ?>">
                              <?php echo htmlspecialchars($task['cstatus']); ?>
                            </span>
                          </td>
                          <td><?php echo htmlspecialchars($task['cstaffid']); ?></td>
                          <td class="text-center">
                            <?php if ($task['cstatus'] === 'Pending'): ?>
                              <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="taskID" value="<?php echo htmlspecialchars($task['ctaskid']); ?>">
                                <input type="hidden" name="status" value="Completed">
                                <button type="submit" class="btn btn-sm btn-success">
                                  <i class="fas fa-check"></i> Mark Done
                                </button>
                              </form>
                            <?php endif; ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this task?');">
                              <input type="hidden" name="action" value="delete_task">
                              <input type="hidden" name="taskID" value="<?php echo htmlspecialchars($task['ctaskid']); ?>">
                              <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                              </button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="6" class="text-center">No tasks found.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
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
  </div>

  
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

 
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/sb-admin-2.min.js"></script>


  <script>
    
    document.getElementById('showTaskForm').addEventListener('click', function () {
      const form = document.getElementById('taskForm');
      form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });

    // Auto-hide alerts after 5 seconds
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
