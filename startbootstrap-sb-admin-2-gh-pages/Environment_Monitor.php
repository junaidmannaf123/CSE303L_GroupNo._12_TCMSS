<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Tortoise Conservation Management System - Environment Monitor">
    <meta name="author" content="">
    <title>Tortoise Conservation - Environment Monitor</title>
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
        .clickable-card:hover .text-success,
        .clickable-card:hover .text-info,
        .clickable-card:hover .text-warning,
        .clickable-card:hover .text-danger,
        .clickable-card:hover .text-primary,
        .clickable-card:hover .text-secondary {
            color: inherit !important;
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="vetdashboard.html">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-turtle"></i>
                </div>
                <div class="sidebar-brand-text mx-3">TCMSS</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item active">
                <a class="nav-link" href="#dashboard-section">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>
            <hr class="sidebar-divider">
            <li class="nav-item active">
                <a class="nav-link" href="Egg_Incubator.php">
                    <i class="fas fa-fw fa-egg"></i>
                    <span>Egg Incubator</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="environment_monitor.php">
                    <i class="fas fa-fw fa-leaf"></i>
                    <span>Environment Monitor</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#assigned-tasks-section">
                    <i class="fas fa-fw fa-tasks"></i>
                    <span>Assigned Tasks</span></a>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">

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
                    <h4 class="ml-3 mt-2 text-success font-weight-bold d-inline-block">Environment Monitor</h4>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Dr. Atika Humayra</span>
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
                        <h1 class="h3 mb-0 text-gray-800">Environment Monitoring Dashboard</h1>
                    </div>

                    <!-- Add New Record Button -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <button class="btn btn-success" data-toggle="modal" data-target="#addRecordModal">
                                <i class="fas fa-plus mr-2"></i>Add New Environmental Record
                            </button>
                        </div>
                    </div>

                    <!-- Dashboard Section (Environmental Data) -->
                    <div class="row" id="dashboard-section">
                        <div class="col-xl-12 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-list-alt mr-2"></i>Environmental Data</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="environmentTable">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Environmental Data ID</th>
                                                    <th>Temperature (°C)</th>
                                                    <th>Humidity (%)</th>
                                                    <th>Status</th>
                                                    <th>Water Quality</th>
                                                    <th>Time Stamp</th>
                                                    <th>Incubator ID</th>
                                                    <th>Enclosure ID</th>
                                                    <th>Staff ID</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="environmentTableBody">
                                                <!-- Data will be populated by JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Tasks Section -->
                    <div class="row" id="assigned-tasks-section">
                        <div class="col-xl-12 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-tasks mr-2"></i>Assigned Tasks</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Task ID</th>
                                                    <th>Task Title</th>
                                                    <th>Description</th>
                                                    <th>Assigned By</th>
                                                    <th>Due Date</th>
                                                    <th>Priority</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>TASK-001</td>
                                                    <td>Monitor Temperature Sensors</td>
                                                    <td>Check all temperature sensors in enclosures 1-5 and report any anomalies</td>
                                                    <td>Manager </td>
                                                    <td>2025-08-02</td>
                                                    <td><span class="badge badge-warning">Medium</span></td>
                                                    <td><span class="badge badge-info">In Progress</span></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-success mr-1">Complete</button>
                                                        <button class="btn btn-sm btn-info">Update</button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>TASK-002</td>
                                                    <td>Water Quality Check</td>
                                                    <td>Test water quality parameters in all enclosures and update records</td>
                                                    <td>Manager </td>
                                                    <td>2025-08-01</td>
                                                    <td><span class="badge badge-danger">High</span></td>
                                                    <td><span class="badge badge-warning">Pending</span></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-success mr-1">Complete</button>
                                                        <button class="btn btn-sm btn-info">Update</button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>TASK-003</td>
                                                    <td>Humidity Monitoring</td>
                                                    <td>Daily humidity readings for incubator rooms and report to supervisor</td>
                                                    <td>Manager </td>
                                                    <td>2025-08-03</td>
                                                    <td><span class="badge badge-success">Low</span></td>
                                                    <td><span class="badge badge-secondary">Not Started</span></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-success mr-1">Complete</button>
                                                        <button class="btn btn-sm btn-info">Update</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add/Edit Record Modal -->
                    <div class="modal fade" id="addRecordModal" tabindex="-1" role="dialog" aria-labelledby="recordModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="recordModalLabel">Add New Environmental Record</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="environmentRecordForm">
                                        <input type="hidden" id="editIndex" value="">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="envDataId">Environmental Data ID</label>
                                                    <input type="text" class="form-control" id="envDataId" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="temperature">Temperature (°C)</label>
                                                    <input type="number" class="form-control" id="temperature" min="-10" max="50" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="humidity">Humidity (%)</label>
                                                    <input type="number" class="form-control" id="humidity" min="0" max="100" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="status">Status</label>
                                                    <select class="form-control" id="status" required>
                                                        <option value="">Select Status</option>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Warning">Warning</option>
                                                        <option value="Critical">Critical</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="waterQuality">Water Quality</label>
                                                    <select class="form-control" id="waterQuality" required>
                                                        <option value="">Select Quality</option>
                                                        <option value="Excellent">Excellent</option>
                                                        <option value="Good">Good</option>
                                                        <option value="Fair">Fair</option>
                                                        <option value="Poor">Poor</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="timeStamp">Time Stamp</label>
                                                    <input type="datetime-local" class="form-control" id="timeStamp" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="incubatorId">Incubator ID</label>
                                                    <input type="text" class="form-control" id="incubatorId" placeholder="e.g., INC-01">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="incubatorId">Enclosure ID</label>
                                                    <input type="text" class="form-control" id="enclosureId" placeholder="e.g., ENC-01">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="staffId">Staff ID</label>
                                                    <input type="text" class="form-control" id="staffId" required>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-success" id="saveRecordBtn">Save Record</button>
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
                    <a class="btn btn-success" href="login.html">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script>
        
        // Environment Monitor CRUD functionality
        let environmentData = [
            {
                id: 'EVD-001',
                temperature: 28,
                humidity: 65,
                status: 'Normal',
                waterQuality: 'Good',
                timeStamp: '2025-07-31T09:00',
                incubatorId: 'INC-01',
                enclosureId: 'ENC-01',
                staffId: 'ST-100'
            },
            {
                id: 'EVD-002',
                temperature: 32,
                humidity: 55,
                status: 'Warning',
                waterQuality: 'Fair',
                timeStamp: '2025-07-31T09:00',
                incubatorId: 'INC-01',
                enclosureId: 'ENC-NA',
                staffId: 'ST-101'
            },
            {
                id: 'EVD-003',
                temperature: 31,
                humidity: 60,
                status: 'Critical',
                waterQuality: 'Poor',
                timeStamp: '2025-07-31T08:00',
                incubatorId: 'INC-NA',
                enclosureId: 'ENC-02',
                staffId: 'ST-102'
            }
        ];

        // Render table
        function renderTable() {
            const tbody = document.getElementById('environmentTableBody');
            tbody.innerHTML = '';
            
            environmentData.forEach((record, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${record.id}</td>
                    <td>${record.temperature}</td>
                    <td>${record.humidity}</td>
                    <td>${getStatusBadge(record.status)}</td>
                    <td>${record.waterQuality}</td>
                    <td>${formatDateTime(record.timeStamp)}</td>
                    <td>${record.incubatorId}</td>
                    <td>${record.enclosureId}</td>
                    <td>${record.staffId}</td>
                    <td>
                        <button class="btn btn-sm btn-info mr-1" onclick="editRecord(${index})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteRecord(${index})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });


        }

        // Get status badge
        function getStatusBadge(status) {
            const badges = {
                'Normal': 'badge-success',
                'Warning': 'badge-warning',
                'Critical': 'badge-danger'
            };
            return `<span class="badge ${badges[status] || 'badge-secondary'}">${status}</span>`;
        }

        // Format datetime
        function formatDateTime(dateTimeStr) {
            const date = new Date(dateTimeStr);
            return date.toLocaleString();
        }

        // Add new record
        function addRecord() {
            document.getElementById('editIndex').value = '';
            document.getElementById('environmentRecordForm').reset();
            document.getElementById('recordModalLabel').textContent = 'Add New Environmental Record';
            $('#addRecordModal').modal('show');
        }

        // Edit record
        function editRecord(index) {
            const record = environmentData[index];
            document.getElementById('editIndex').value = index;
            document.getElementById('envDataId').value = record.id;
            document.getElementById('temperature').value = record.temperature;
            document.getElementById('humidity').value = record.humidity;
            document.getElementById('status').value = record.status;
            document.getElementById('waterQuality').value = record.waterQuality;
            document.getElementById('timeStamp').value = record.timeStamp;
            document.getElementById('incubatorId').value = record.incubatorId;
            document.getElementById('enclosureId').value = record.enclosureId;
            document.getElementById('staffId').value = record.staffId;
            
            document.getElementById('recordModalLabel').textContent = 'Edit Environmental Record';
            $('#addRecordModal').modal('show');
        }

        // Delete record
        function deleteRecord(index) {
            if (confirm('Are you sure you want to delete this record?')) {
                environmentData.splice(index, 1);
                renderTable();
            }
        }

        // Save record
        function saveRecord() {
            const editIndex = document.getElementById('editIndex').value;
            const formData = {
                id: document.getElementById('envDataId').value,
                temperature: parseInt(document.getElementById('temperature').value),
                humidity: parseInt(document.getElementById('humidity').value),
                status: document.getElementById('status').value,
                waterQuality: document.getElementById('waterQuality').value,
                timeStamp: document.getElementById('timeStamp').value,
                incubatorId: document.getElementById('incubatorId').value,
                enclosureId: document.getElementById('enclosureId').value,
                staffId: document.getElementById('staffId').value
            };

            if (editIndex === '') {
                // Add new record
                environmentData.push(formData);
            } else {
                // Update existing record
                environmentData[editIndex] = formData;
            }

            renderTable();
            $('#addRecordModal').modal('hide');
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            renderTable();
            
            // Save button click
            document.getElementById('saveRecordBtn').addEventListener('click', saveRecord);
            
            // Modal close - reset form
            $('#addRecordModal').on('hidden.bs.modal', function() {
                document.getElementById('environmentRecordForm').reset();
                document.getElementById('editIndex').value = '';
            });


        });




            


    </script>
</body>
</html>