<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Tortoise Conservation Management System - Tortoise List">
    <meta name="author" content="">
    <title>Tortoise Conservation - Tortoise List</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        .status-healthy { color: #28a745; }
        .status-sick { color: #dc3545; }
        .status-recovering { color: #ffc107; }
        .status-critical { color: #dc3545; font-weight: bold; }
        .btn-sm { margin: 2px; }
        .table-responsive { max-height: 600px; overflow-y: auto; }
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
            <li class="nav-item">
                <a class="nav-link" href="vetdashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Vet Tools</div>
            <li class="nav-item active">
                <a class="nav-link" href="tortoise-list.php">
                    <i class="fas fa-fw fa-list"></i>
                    <span>View Tortoise List</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="health-records.php">
                    <i class="fas fa-fw fa-notes-medical"></i>
                    <span>Health Records</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="assigned_tasks.html">
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
                    <h4 class="ml-3 mt-2 text-success font-weight-bold d-inline-block">Tortoise List Management</h4>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Dr. Atika Humaira</span>
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Tortoise Registry</h1>
                        <div>
                            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addTortoiseModal">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Add New Tortoise
                            </button>
                        </div>
                    </div>
                    
                    <!-- Search and Filter Section -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search by ID or Name...">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary btn-sm" onclick="filterTable()">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted">Showing <span id="recordCount">30</span> of 30 tortoises</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tortoise List Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-turtle"></i> Tortoise Registry</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="tortoiseTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Age (Years)</th>
                                            <th>Gender</th>
                                            <th>Enclosure</th>
                                            <th>Species</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tortoiseTableBody">
                                        <!-- Tortoise data will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Tortoise Modal -->
    <div class="modal fade" id="addTortoiseModal" tabindex="-1" role="dialog" aria-labelledby="addTortoiseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTortoiseModalLabel">Add New Tortoise</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addTortoiseForm">
                        <div class="form-group">
                            <label for="tortoiseName">Name</label>
                            <input type="text" class="form-control" id="tortoiseName" required>
                        </div>
                        <div class="form-group">
                            <label for="tortoiseSpecies">Species</label>
                            <select class="form-control" id="tortoiseSpecies" required>
                                <option value="">Select Species</option>
                                <option value="Asian Giant Tortoise">Asian Giant Tortoise</option>
                                <option value="Arakan Forest Turtle">Arakan Forest Turtle</option>
                                <option value="Elongated Tortoise">Elongated Tortoise</option>
                                <option value="Keeled Box Turtle">Keeled Box Turtle</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tortoiseGender">Gender</label>
                            <select class="form-control" id="tortoiseGender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="juvenile">Juvenile</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tortoiseEnclosure">Enclosure</label>
                            <select class="form-control" id="tortoiseEnclosure" required>
                                <option value="">Select Enclosure</option>
                                <option value="EN-1">EN-1 (Outdoor - North Zone)</option>
                                <option value="EN-2">EN-2 (Outdoor - East Wing)</option>
                                <option value="LAB">LAB (Indoor - Research Block A)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tortoiseAge">Age (Years)</label>
                            <input type="number" class="form-control" id="tortoiseAge" min="0" max="200" required>
                        </div>
                        <div class="form-group">
                            <label for="tortoiseHealth">Health Status</label>
                            <select class="form-control" id="tortoiseHealth" required>
                                <option value="">Select Health Status</option>
                                <option value="Healthy">Healthy</option>
                                <option value="Sick">Sick</option>
                                <option value="Recovering">Recovering</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="addTortoise()">Add Tortoise</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Tortoise Modal -->
    <div class="modal fade" id="editTortoiseModal" tabindex="-1" role="dialog" aria-labelledby="editTortoiseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTortoiseModalLabel">Edit Tortoise</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editTortoiseForm">
                        <input type="hidden" id="editTortoiseId">
                        <div class="form-group">
                            <label for="editTortoiseName">Name</label>
                            <input type="text" class="form-control" id="editTortoiseName" required>
                        </div>
                        <div class="form-group">
                            <label for="editTortoiseSpecies">Species</label>
                            <select class="form-control" id="editTortoiseSpecies" required>
                                <option value="">Select Species</option>
                                <option value="Asian Giant Tortoise">Asian Giant Tortoise</option>
                                <option value="Arakan Forest Turtle">Arakan Forest Turtle</option>
                                <option value="Elongated Tortoise">Elongated Tortoise</option>
                                <option value="Keeled Box Turtle">Keeled Box Turtle</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editTortoiseGender">Gender</label>
                            <select class="form-control" id="editTortoiseGender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="juvenile">Juvenile</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editTortoiseEnclosure">Enclosure</label>
                            <select class="form-control" id="editTortoiseEnclosure" required>
                                <option value="">Select Enclosure</option>
                                <option value="EN-1">EN-1 (Outdoor - North Zone)</option>
                                <option value="EN-2">EN-2 (Outdoor - East Wing)</option>
                                <option value="LAB">LAB (Indoor - Research Block A)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editTortoiseAge">Age (Years)</label>
                            <input type="number" class="form-control" id="editTortoiseAge" min="0" max="200" required>
                        </div>
                        <div class="form-group">
                            <label for="editTortoiseHealth">Health Status</label>
                            <select class="form-control" id="editTortoiseHealth" required>
                                <option value="">Select Health Status</option>
                                <option value="Healthy">Treatment</option>
                                <option value="Sick">Vaccination</option>
                                <option value="Recovering">Checkup</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateTortoise()">Update Tortoise</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteTortoiseModal" tabindex="-1" role="dialog" aria-labelledby="deleteTortoiseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTortoiseModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete tortoise <span id="deleteTortoiseName"></span>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="deleteTortoise()">Delete</button>
                </div>
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
    <!-- DataTables -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        // Real tortoise data from database
        let tortoises = [];
        const species = ['Asian Giant Tortoise', 'Arakan Forest Turtle', 'Elongated Tortoise', 'Keeled Box Turtle'];
        
        // Fetch tortoises from database
        async function fetchTortoises() {
            try {
                const response = await fetch('api/get_tortoises.php');
                const result = await response.json();
                
                if (result.success) {
                    tortoises = result.data;
                    populateTable();
                } else {
                    console.error('Error fetching tortoises:', result.error);
                    // Fallback to sample data if API fails
                    generateSampleTortoises();
                }
            } catch (error) {
                console.error('Error fetching tortoises:', error);
                // Fallback to sample data if API fails
                generateSampleTortoises();
            }
        }

        // Fallback sample data generation
        function generateSampleTortoises() {
            const sampleNames = ['Shella', 'Boulder', 'Mossy', 'Pebble', 'Spike', 'Hazel', 'Drift', 'Clover', 'Terra', 'Stone'];
            const sampleSpecies = ['Elongated Tortoise', 'Arakan Forest Turtle', 'Asian Giant Tortoise', 'Keeled Box Turtle'];
            
            for (let i = 1; i <= 10; i++) {
                const tortoise = {
                    ctortoiseid: String(i).padStart(3, '0'),
                    cname: sampleNames[i - 1],
                    nage: Math.floor(Math.random() * 80) + 1,
                    cgender: ['male', 'female', 'juvenile'][Math.floor(Math.random() * 3)],
                    cenclosureid: ['EN-1', 'EN-2', 'LAB'][Math.floor(Math.random() * 3)],
                    cspeciesid: `S${Math.floor(Math.random() * 4) + 1}`,
                    species_name: sampleSpecies[Math.floor(Math.random() * 4)],
                    scientific_name: 'Sample Scientific Name',
                    cenclosuretype: ['Outdoor', 'Indoor'][Math.floor(Math.random() * 2)],
                    clocation: ['North Zone', 'East Wing', 'Research Block A'][Math.floor(Math.random() * 3)],
                    csize: '50x40m'
                };
                tortoises.push(tortoise);
            }
            populateTable();
        }

        // Populate table
        function populateTable(data = tortoises) {
            const tbody = document.getElementById('tortoiseTableBody');
            tbody.innerHTML = '';
            
            data.forEach(tortoise => {
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td><strong>${tortoise.ctortoiseid}</strong></td>
                    <td>${tortoise.cname || '-'}</td>
                    <td>${tortoise.nage || '-'}</td>
                    <td><span class="badge badge-info">${tortoise.cgender || '-'}</span></td>
                    <td>
                        <small class="text-muted">${tortoise.cenclosureid || '-'}</small><br>
                        <span class="badge badge-secondary">${tortoise.cenclosuretype || '-'}</span>
                    </td>
                    <td>
                        <small class="text-muted">${tortoise.cspeciesid || '-'}</small><br>
                        <span class="badge badge-primary">${tortoise.species_name || '-'}</span>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="editTortoise('${tortoise.ctortoiseid}')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                                                <button class="btn btn-danger btn-sm" onclick="confirmDelete('${tortoise.ctortoiseid}', '${tortoise.cname || 'Unknown'}')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            document.getElementById('recordCount').textContent = data.length;
        }

        // Get health status CSS class
        function getHealthClass(status) {
            switch(status) {
                case 'Healthy': return 'status-healthy';
                case 'Sick': return 'status-sick';
                case 'Recovering': return 'status-recovering';
                case 'Critical': return 'status-critical';
                default: return '';
            }
        }

        // Filter table
        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            const filteredData = tortoises.filter(tortoise => {
                const matchesSearch = tortoise.ctortoiseid.toLowerCase().includes(searchTerm) || 
                                    (tortoise.cname && tortoise.cname.toLowerCase().includes(searchTerm));
                return matchesSearch;
            });
            
            populateTable(filteredData);
        }

        // Add new tortoise
        async function addTortoise() {
            const name = document.getElementById('tortoiseName').value;
            const species = document.getElementById('tortoiseSpecies').value;
            const gender = document.getElementById('tortoiseGender').value;
            const enclosure = document.getElementById('tortoiseEnclosure').value;
            const age = parseInt(document.getElementById('tortoiseAge').value);
            const healthStatus = document.getElementById('tortoiseHealth').value;
            
            if (!name || !species || !gender || !enclosure || !age || !healthStatus) {
                alert('Please fill in all fields');
                return;
            }
            
            try {
                const response = await fetch('api/add_tortoise.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: name,
                        species: species,
                        gender: gender,
                        enclosure: enclosure,
                        age: age,
                        health_status: healthStatus
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Tortoise added successfully!');
                    $('#addTortoiseModal').modal('hide');
                    document.getElementById('addTortoiseForm').reset();
                    // Refresh the data
                    await fetchTortoises();
                } else {
                    alert('Error adding tortoise: ' + result.error);
                }
            } catch (error) {
                console.error('Error adding tortoise:', error);
                alert('Error adding tortoise. Please try again.');
            }
        }

        // Edit tortoise
        function editTortoise(id) {
            const tortoise = tortoises.find(t => t.ctortoiseid === id);
            if (!tortoise) return;
            
            document.getElementById('editTortoiseId').value = tortoise.ctortoiseid;
            document.getElementById('editTortoiseName').value = tortoise.cname || '';
            document.getElementById('editTortoiseSpecies').value = tortoise.species_name || 'Asian Giant Tortoise';
            document.getElementById('editTortoiseGender').value = tortoise.cgender || 'male';
            document.getElementById('editTortoiseEnclosure').value = tortoise.cenclosureid || 'EN-1';
            document.getElementById('editTortoiseAge').value = tortoise.nage || '';
            document.getElementById('editTortoiseHealth').value = 'Healthy'; // Default value since health status is not in the main table
            
            $('#editTortoiseModal').modal('show');
        }

        // Update tortoise
        async function updateTortoise() {
            const id = document.getElementById('editTortoiseId').value;
            const name = document.getElementById('editTortoiseName').value;
            const species = document.getElementById('editTortoiseSpecies').value;
            const gender = document.getElementById('editTortoiseGender').value;
            const enclosure = document.getElementById('editTortoiseEnclosure').value;
            const age = parseInt(document.getElementById('editTortoiseAge').value);
            const healthStatus = document.getElementById('editTortoiseHealth').value;
            
            if (!name || !species || !gender || !enclosure || !age || !healthStatus) {
                alert('Please fill in all fields');
                return;
            }
            
            try {
                const response = await fetch('api/update_tortoise.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        name: name,
                        species: species,
                        gender: gender,
                        enclosure: enclosure,
                        age: age,
                        health_status: healthStatus
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Tortoise updated successfully!');
                    $('#editTortoiseModal').modal('hide');
                    // Refresh the data
                    await fetchTortoises();
                } else {
                    alert('Error updating tortoise: ' + result.error);
                }
            } catch (error) {
                console.error('Error updating tortoise:', error);
                alert('Error updating tortoise. Please try again.');
            }
        }

        // Confirm delete
        function confirmDelete(id, name) {
            document.getElementById('deleteTortoiseName').textContent = name;
            document.getElementById('deleteTortoiseModal').setAttribute('data-tortoise-id', id);
            $('#deleteTortoiseModal').modal('show');
        }

        // Delete tortoise
        async function deleteTortoise() {
            const id = document.getElementById('deleteTortoiseModal').getAttribute('data-tortoise-id');
            
            try {
                const response = await fetch('api/delete_tortoise.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Tortoise deleted successfully!');
                    $('#deleteTortoiseModal').modal('hide');
                    // Refresh the data
                    await fetchTortoises();
                } else {
                    alert('Error deleting tortoise: ' + result.error);
                }
            } catch (error) {
                console.error('Error deleting tortoise:', error);
                alert('Error deleting tortoise. Please try again.');
            }
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', filterTable);

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            fetchTortoises();
        });
    </script>
</body>
</html> 