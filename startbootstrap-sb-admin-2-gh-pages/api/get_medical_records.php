<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    // Optional filters via query params
    $params = [];
    $where = [];

    if (!empty($_GET['tortoiseId'])) {
        $where[] = 'mr.ctortoiseid = :tortoiseId';
        $params[':tortoiseId'] = $_GET['tortoiseId'];
    }
    if (!empty($_GET['type'])) {
        $where[] = 'mr.ctype = :type';
        $params[':type'] = $_GET['type'];
    }
    if (!empty($_GET['dateFrom'])) {
        $where[] = 'mr.ddate >= :dateFrom';
        $params[':dateFrom'] = $_GET['dateFrom'];
    }
    if (!empty($_GET['dateTo'])) {
        $where[] = 'mr.ddate <= :dateTo';
        $params[':dateTo'] = $_GET['dateTo'];
    }
    if (!empty($_GET['notes'])) {
        $where[] = '(mr.cdiagnosis LIKE :notes OR mr.ctreatment LIKE :notes)';
        $params[':notes'] = '%' . $_GET['notes'] . '%';
    }

    $whereSql = '';
    if (!empty($where)) {
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

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
            mr.ctortoiseid,
            t.cname AS tortoise_name,
            s.cname AS staff_name
        FROM tblmedicalrecords mr
        LEFT JOIN tbltortoise t ON mr.ctortoiseid = t.ctortoiseid
        LEFT JOIN tblstaffmember s ON mr.cstaffid = s.cstaffid
        $whereSql
        ORDER BY mr.drecordingdate DESC, mr.crecordid ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'count' => count($records),
        'data' => $records
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>


