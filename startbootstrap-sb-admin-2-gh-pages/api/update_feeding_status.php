<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
      
        if (empty($input['feedingID']) || empty($input['status'])) {
            throw new Exception('Feeding ID and status are required');
        }

       
        if (!in_array($input['status'], ['Pending', 'Done'])) {
            throw new Exception('Status must be either "Pending" or "Done"');
        }

       
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM tblfeedingschedule WHERE cfeedingid = ?");
        $checkStmt->execute([$input['feedingID']]);
        
        if ($checkStmt->fetchColumn() == 0) {
            throw new Exception('Feeding schedule not found');
        }

        
        $stmt = $pdo->prepare("UPDATE tblfeedingschedule SET cstatus = ? WHERE cfeedingid = ?");
        $stmt->execute([$input['status'], $input['feedingID']]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Feeding status updated successfully',
            'feedingID' => $input['feedingID'],
            'newStatus' => $input['status']
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
