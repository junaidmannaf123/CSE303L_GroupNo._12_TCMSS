<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    // Fetch tortoise data with species and enclosure information
    $query = "
        SELECT 
            t.ctortoiseid,
            t.cname,
            t.nage,
            t.cgender,
            t.cenclosureid,
            t.cspeciesid,
            s.ccommonname as species_name,
            s.cscientificname as scientific_name,
            e.cenclosuretype,
            e.clocation,
            e.csize
        FROM tbltortoise t
        LEFT JOIN tblspecies s ON t.cspeciesid = s.cspeciesid
        LEFT JOIN tblenclosure e ON t.cenclosureid = e.cenclosureid
        ORDER BY t.ctortoiseid
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tortoises = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $tortoises,
        'count' => count($tortoises)
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
