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
                        <h1 class="h3 mb-0 text-gray-800">Health Records</h1>
                        <a href="#" class="btn btn-success btn-sm shadow-sm" data-toggle="modal" data-target="#addHealthRecordModal"><i class="fas fa-plus fa-sm text-white-50"></i> Add New Record</a>
                    </div>
                    <!-- Filter Section -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-filter mr-2"></i>Filter Records
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="filterForm">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filterTortoiseId" class="font-weight-bold text-gray-800">Tortoise ID</label>
                                            <input type="text" class="form-control" id="filterTortoiseId" placeholder="Enter tortoise ID">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filterType" class="font-weight-bold text-gray-800">Type</label>
                                            <select class="form-control" id="filterType">
                                                <option value="">All Types</option>
                                                <option value="Illness">Illness</option>
                                                <option value="Injury">Injury</option>
                                                <option value="Checkup">Checkup</option>
                                                <option value="Emergency">Emergency</option>
                                                <option value="Monitoring">Monitoring</option>
                                                <option value="Infection">Infection</option>
                                                <option value="Surgery">Surgery</option>
                                                <option value="Recovery">Recovery</option>
                                                <option value="Nutrition">Nutrition</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filterDateFrom" class="font-weight-bold text-gray-800">Date From</label>
                                            <input type="date" class="form-control" id="filterDateFrom">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filterDateTo" class="font-weight-bold text-gray-800">Date To</label>
                                            <input type="date" class="form-control" id="filterDateTo">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="filterNotes" class="font-weight-bold text-gray-800">Search in Notes</label>
                                            <input type="text" class="form-control" id="filterNotes" placeholder="Search in notes...">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-800">Actions</label>
                                            <div class="d-flex">
                                                <button type="button" class="btn btn-success btn-sm mr-2" onclick="applyFilter()">
                                                    <i class="fas fa-search mr-1"></i>Apply Filter
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilter()">
                                                    <i class="fas fa-times mr-1"></i>Clear Filter
                                                </button>
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
                                <span id="recordCount">Showing 50 records</span>
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
    <!-- Add Health Record Modal -->
    <div class="modal fade" id="addHealthRecordModal" tabindex="-1" role="dialog" aria-labelledby="addHealthRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success" id="addHealthRecordModalLabel">
                        <i class="fas fa-plus-circle mr-2"></i>Add New Health Record
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addHealthRecordForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="crecordid" class="font-weight-bold text-gray-800">Record ID</label>
                                    <input type="text" class="form-control" id="crecordid" name="crecordid" placeholder="Auto when adding" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="drecordingdate" class="font-weight-bold text-gray-800">Recording Date *</label>
                                    <input type="date" class="form-control" id="drecordingdate" name="drecordingdate" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ctortoiseid" class="font-weight-bold text-gray-800">Tortoise ID *</label>
                                    <input type="text" class="form-control" id="ctortoiseid" name="ctortoiseid" required placeholder="e.g. 001">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cdiagnosis" class="font-weight-bold text-gray-800">Diagnosis</label>
                                    <input type="text" class="form-control" id="cdiagnosis" name="cdiagnosis" placeholder="Enter diagnosis">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ctreatment" class="font-weight-bold text-gray-800">Treatment</label>
                                    <input type="text" class="form-control" id="ctreatment" name="ctreatment" placeholder="Enter treatment">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ctype" class="font-weight-bold text-gray-800">Type *</label>
                                    <select class="form-control" id="ctype" name="ctype" required>
                                        <option value="">Select type</option>
                                        <option value="Illness">Illness</option>
                                        <option value="Injury">Injury</option>
                                        <option value="Checkup">Checkup</option>
                                        <option value="Emergency">Emergency</option>
                                        <option value="Monitoring">Monitoring</option>
                                        <option value="Infection">Infection</option>
                                        <option value="Surgery">Surgery</option>
                                        <option value="Recovery">Recovery</option>
                                        <option value="Nutrition">Nutrition</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ddate" class="font-weight-bold text-gray-800">Date</label>
                                    <input type="date" class="form-control" id="ddate" name="ddate">
                                </div>
                            </div>
                            <div class="col-md-4">
                        <div class="form-group">
                                    <label for="cvaccinationstatus" class="font-weight-bold text-gray-800">Vaccination Status</label>
                                    <select class="form-control" id="cvaccinationstatus" name="cvaccinationstatus">
                                        <option value="">Select</option>
                                        <option>Up-to-date</option>
                                        <option>Pending</option>
                                        <option>Overdue</option>
                                    </select>
                        </div>
                        </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="dcheckdate" class="font-weight-bold text-gray-800">Check Date</label>
                                    <input type="date" class="form-control" id="dcheckdate" name="dcheckdate">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="dchecktime" class="font-weight-bold text-gray-800">Check Time</label>
                                    <input type="time" class="form-control" id="dchecktime" name="dchecktime">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cstaffid" class="font-weight-bold text-gray-800">Staff ID *</label>
                                    <input type="text" class="form-control" id="cstaffid" name="cstaffid" placeholder="e.g. SM004" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success btn-sm" type="button" onclick="saveHealthRecord()">
                        <i class="fas fa-save mr-1"></i>Save Record
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Add Health Record Modal -->
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
    
    <!-- Health Record Modal JavaScript -->
    <script>
        // Set default date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('drecordingdate').value = today;
        });

        // ===== API-driven loading and CRUD (appended) =====
        async function apiFetchMedicalRecords(filters = {}) {
            const params = new URLSearchParams(filters);
            const res = await fetch('api/get_medical_records.php' + (params.toString() ? ('?' + params.toString()) : ''));
            const json = await res.json();
            if (!json.success) throw new Error(json.error || 'Failed to load records');
            return json.data;
        }

        function inDefaultRange(id) {
            if (!id || id.length < 5) return false;
            const num = parseInt(String(id).replace(/^[^0-9]+/, ''), 10);
            return num >= 1 && num <= 30;
        }

        function vaccinationBadge(text) {
            if (!text) return '';
            const t = String(text).toLowerCase();
            if (t.includes('overdue')) return '<span class="badge badge-danger">' + text + '</span>';
            if (t.includes('pending') || t.includes('due')) return '<span class="badge badge-warning">' + text + '</span>';
            return '<span class="badge badge-success">' + text + '</span>';
        }

        function renderRecords(records) {
            const tbody = document.getElementById('healthRecordsTableBody');
            tbody.innerHTML = '';
            records.forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + (r.crecordid ?? '') + '</td>' +
                    '<td>' + (r.drecordingdate ?? '') + '</td>' +
                    '<td>' + (r.cdiagnosis ?? '') + '</td>' +
                    '<td>' + (r.ctreatment ?? '') + '</td>' +
                    '<td>' + (r.ctype ?? '') + '</td>' +
                    '<td>' + (r.ddate ?? '') + '</td>' +
                    '<td>' + vaccinationBadge(r.cvaccinationstatus ?? '') + '</td>' +
                    '<td>' + (r.dcheckdate ?? '') + '</td>' +
                    '<td>' + (r.dchecktime ?? '') + '</td>' +
                    '<td>' + (r.cstaffid ?? '') + '</td>' +
                    '<td>' + (r.ctortoiseid ?? '') + '</td>' +
                    '<td><button class="btn btn-sm btn-primary mr-1" onclick="editRecord(this)"><i class="fas fa-edit"></i> Edit</button> <button class="btn btn-sm btn-danger" onclick="deleteRecord(this)"><i class="fas fa-trash"></i> Delete</button></td>';
                tbody.appendChild(tr);
            });
            document.getElementById('recordCount').textContent = 'Showing ' + records.length + ' records';
        }

        async function loadAllSorted() {
            const all = await apiFetchMedicalRecords();
            const parseNum = id => {
                const m = String(id || '').match(/(\d+)/);
                return m ? parseInt(m[1], 10) : Number.MAX_SAFE_INTEGER;
            };
            const first = all.filter(r => {
                const n = parseNum(r.crecordid);
                return n >= 1 && n <= 30;
            }).sort((a, b) => parseNum(a.crecordid) - parseNum(b.crecordid));
            const rest = all.filter(r => {
                const n = parseNum(r.crecordid);
                return n > 30;
            }).sort((a, b) => parseNum(a.crecordid) - parseNum(b.crecordid));
            renderRecords([...first, ...rest]);
        }

        async function loadRecordsFromApiWithFilters() {
            const tortoiseId = document.getElementById('filterTortoiseId').value;
            const type = document.getElementById('filterType').value;
            const dateFrom = document.getElementById('filterDateFrom').value;
            const dateTo = document.getElementById('filterDateTo').value;
            const notes = document.getElementById('filterNotes').value;
            const filters = {};
            if (tortoiseId) filters.tortoiseId = tortoiseId;
            if (type) filters.type = type;
            if (dateFrom) filters.dateFrom = dateFrom;
            if (dateTo) filters.dateTo = dateTo;
            if (notes) filters.notes = notes;
            const data = await apiFetchMedicalRecords(filters);
            renderRecords(data);
        }

        // Override filter functions to use API and default range on clear
        applyFilter = function() {
            loadRecordsFromApiWithFilters().catch(console.error);
        }
        clearFilter = function() {
            document.getElementById('filterForm').reset();
            loadAllSorted().catch(console.error);
        }

        // Modal helpers
        function setModalTitle(text, iconClass) {
            const title = document.getElementById('addHealthRecordModalLabel');
            if (title) title.innerHTML = '<i class="' + (iconClass || 'fas fa-plus-circle') + ' mr-2"></i>' + text;
        }
        function openAddModal() {
            setModalTitle('Add New Health Record', 'fas fa-plus-circle');
            document.getElementById('crecordid').value = '';
            $('#addHealthRecordModal').modal('show');
        }

        // Wire the existing "Add New Record" button to our add modal handler
        document.addEventListener('DOMContentLoaded', function() {
            const addBtn = document.querySelector('[data-target="#addHealthRecordModal"]');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    openAddModal();
                });
            }
        });

        // Save (create or update) via API using table columns
        saveHealthRecord = async function() {
            const form = document.getElementById('addHealthRecordForm');
            const payload = {
                crecordid: document.getElementById('crecordid').value || undefined,
                drecordingdate: document.getElementById('drecordingdate').value,
                cdiagnosis: document.getElementById('cdiagnosis').value || null,
                ctreatment: document.getElementById('ctreatment').value || null,
                ctype: document.getElementById('ctype').value,
                ddate: document.getElementById('ddate').value || null,
                cvaccinationstatus: document.getElementById('cvaccinationstatus').value || null,
                dcheckdate: document.getElementById('dcheckdate').value || null,
                dchecktime: document.getElementById('dchecktime').value || null,
                cstaffid: document.getElementById('cstaffid').value,
                ctortoiseid: document.getElementById('ctortoiseid').value
            };

            if (!payload.drecordingdate || !payload.ctype || !payload.ctortoiseid || !payload.cstaffid) {
                alert('Please fill in required fields.');
                return;
            }

            const isUpdate = !!payload.crecordid;
            try {
                const url = isUpdate ? 'api/update_medical_record.php' : 'api/add_medical_record.php';
                const method = isUpdate ? 'PUT' : 'POST';
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (!json.success) throw new Error(json.error || 'Operation failed');
                $('#addHealthRecordModal').modal('hide');
                await loadAllSorted();
            } catch (e) {
                alert(e.message);
            }
        }

        // Implement edit to prefill and switch to update mode
        editRecord = function(btn) {
            const row = btn.closest('tr');
            const cells = row.getElementsByTagName('td');
            if (!cells.length) return;
            setModalTitle('Edit Health Record', 'fas fa-edit');
            document.getElementById('crecordid').value = cells[0].textContent.trim();
            document.getElementById('drecordingdate').value = cells[1].textContent.trim();
            document.getElementById('cdiagnosis').value = cells[2].textContent.trim();
            document.getElementById('ctreatment').value = cells[3].textContent.trim();
            document.getElementById('ctype').value = cells[4].textContent.trim();
            document.getElementById('ddate').value = cells[5].textContent.trim();
            // vaccination cell contains badge; use innerText
            document.getElementById('cvaccinationstatus').value = cells[6].innerText.trim();
            document.getElementById('dcheckdate').value = cells[7].textContent.trim();
            document.getElementById('dchecktime').value = cells[8].textContent.trim();
            document.getElementById('cstaffid').value = cells[9].textContent.trim();
            document.getElementById('ctortoiseid').value = cells[10].textContent.trim();
            $('#addHealthRecordModal').modal('show');
        }

        // Delete via API
        deleteRecord = async function(btn) {
            const row = btn.closest('tr');
            const recordId = row && row.cells && row.cells[0] ? row.cells[0].textContent.trim() : '';
            if (!recordId) return;
            if (!confirm('Delete record ' + recordId + '?')) return;
            try {
                const res = await fetch('api/delete_medical_record.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ crecordid: recordId })
                });
                const json = await res.json();
                if (!json.success) throw new Error(json.error || 'Delete failed');
                await loadAllSorted();
            } catch (e) {
                alert(e.message);
            }
        }

        // Initial load: MR001–MR030
        document.addEventListener('DOMContentLoaded', function() {
            loadAllSorted().catch(console.error);
        });
    </script>
</body>
</html> 