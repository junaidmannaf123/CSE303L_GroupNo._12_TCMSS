<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    // Query to get all feeding schedules
    $stmt = $pdo->prepare("
        SELECT 
            cfeedingid,
            DATE_FORMAT(ddate, '%Y-%m-%d') as ddate,
            TIME_FORMAT(dtime, '%H:%i') as dtime,
            cdietnotes,
            cstaffid,
            cenclosureid,
            COALESCE(cstatus, 'Pending') as cstatus
        FROM tblfeedingschedule 
        ORDER BY ddate DESC, dtime DESC
    ");
    
    $stmt->execute();
    $feedingSchedules = $stmt->fetchAll();
    
    echo json_encode([
        'status' => 'success',
        'data' => $feedingSchedules
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>