<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    $required_fields = ['name', 'species', 'age', 'health_status'];
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
    
    // Generate new tortoise ID
    $stmt = $pdo->query("SELECT MAX(CAST(ctortoiseid AS UNSIGNED)) as max_id FROM tbltortoise");
    $result = $stmt->fetch();
    $new_id = str_pad(($result['max_id'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
    
    // Determine gender (random for demo, you can modify this)
    $gender = ['male', 'female', 'juvenile'][array_rand(['male', 'female', 'juvenile'])];
    
    // Assign enclosure (round-robin assignment)
    $enclosures = ['EN-1', 'EN-2', 'LAB'];
    $enclosure_id = $enclosures[array_rand($enclosures)];
    
    // Insert new tortoise
    $query = "
        INSERT INTO tbltortoise (ctortoiseid, cname, nage, cgender, cenclosureid, cspeciesid)
        VALUES (:id, :name, :age, :gender, :enclosure, :species)
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':id' => $new_id,
        ':name' => $input['name'],
        ':age' => $input['age'],
        ':gender' => $gender,
        ':enclosure' => $enclosure_id,
        ':species' => $species_id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Tortoise added successfully',
        'tortoise_id' => $new_id
    ]);
    
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
