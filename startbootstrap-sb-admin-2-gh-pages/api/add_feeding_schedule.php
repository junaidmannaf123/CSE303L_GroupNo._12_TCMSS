<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        if (empty($input['feedingID']) || empty($input['date']) || empty($input['time']) || 
            empty($input['staffID']) || empty($input['enclosureID'])) {
            throw new Exception('All required fields must be provided');
        }

        // Check if feeding ID already exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM tblfeedingschedule WHERE cfeedingid = ?");
        $checkStmt->execute([$input['feedingID']]);
        
        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception('Feeding ID already exists');
        }

        // Insert new feeding schedule
        $stmt = $pdo->prepare("
            INSERT INTO tblfeedingschedule 
            (cfeedingid, ddate, dtime, cdietnotes, cstaffid, cenclosureid) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $input['feedingID'],
            $input['date'],
            $input['time'],
            $input['dietNotes'] ?? null,
            $input['staffID'],
            $input['enclosureID']
        ]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Feeding schedule added successfully',
            'feedingID' => $input['feedingID']
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    } catch(Exception $e) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}
?>
