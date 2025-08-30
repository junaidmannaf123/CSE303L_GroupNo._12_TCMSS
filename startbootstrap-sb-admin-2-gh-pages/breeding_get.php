<?php
require_once __DIR__ . '/config/database.php';

// Optional query params: search, limit, offset
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$sql = "SELECT br.cbreedingid,
               br.nincubationperiod,
               br.dstartdate,
               br.denddate,
               br.neggscount,
               br.nhatchingservicerate,
               br.cstaffid,
               sm.cname AS staff_name,
               br.ctortoiseid,
               tt.cname AS tortoise_name
        FROM tblbreedingrecord br
        LEFT JOIN tblstaffmember sm ON sm.cstaffid = br.cstaffid
        LEFT JOIN tbltortoise tt ON tt.ctortoiseid = br.ctortoiseid";
$params = [];
$types = '';
$wheres = [];

if ($search !== '') {
	$wheres[] = '(br.cbreedingid LIKE CONCAT("%", ?, "%")
		OR br.cstaffid LIKE CONCAT("%", ?, "%")
		OR br.ctortoiseid LIKE CONCAT("%", ?, "%"))';
	$params[] = $search; $types .= 's';
	$params[] = $search; $types .= 's';
	$params[] = $search; $types .= 's';
}

if (!empty($wheres)) {
	$sql .= ' WHERE ' . implode(' AND ', $wheres);
}

$sql .= ' ORDER BY br.cbreedingid ASC';

if ($limit > 0) {
	$sql .= ' LIMIT ? OFFSET ?';
	$params[] = $limit; $types .= 'i';
	$params[] = $offset; $types .= 'i';
}

$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
	send_json(['success' => false, 'error' => $mysqli->error], 500);
}

if (count($params) > 0) {
	$stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
	send_json(['success' => false, 'error' => $stmt->error], 500);
}

$result = $stmt->get_result();
$rows = [];
while ($row = $result->fetch_assoc()) {
	$rows[] = $row;
}

send_json(['success' => true, 'data' => $rows]);
