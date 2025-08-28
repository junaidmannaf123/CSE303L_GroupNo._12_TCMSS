<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $required_fields = ['id', 'name', 'species', 'age', 'health_status'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Map species names to species IDs
    $species_map = [
        'Asian Giant Tortoise' => 'S1',
        'Arakan Forest Turtle' => 'S2',
        'Elongated Tortoise' => 'S3',
        'Keeled Box Turtle' => 'S4'
    ];
    
    $species_id = $species_map[$input['species']] ?? 'S1';
    
    // Check if tortoise exists
    $stmt = $pdo->prepare("SELECT ctortoiseid FROM tbltortoise WHERE ctortoiseid = :id");
    $stmt->execute([':id' => $input['id']]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Tortoise not found');
    }
    
    // Update tortoise
    $query = "
        UPDATE tbltortoise 
        SET cname = :name, nage = :age, cspeciesid = :species
        WHERE ctortoiseid = :id
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':id' => $input['id'],
        ':name' => $input['name'],
        ':age' => $input['age'],
        ':species' => $species_id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Tortoise updated successfully'
    ]);
    
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
