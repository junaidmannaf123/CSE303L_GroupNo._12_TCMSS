<?php
require_once __DIR__ . '/config/database.php';

try {
    // Run the query to get egg data
    $sql = "SELECT ceggid, nweight, nlength, nwidth, ceggcondition, cincubatorid, cbreedingid FROM tbleggdetails";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        send_json(['success' => false, 'error' => 'Database prepare error: ' . $mysqli->error]);
    }
    
    if (!$stmt->execute()) {
        send_json(['success' => false, 'error' => 'Database execute error: ' . $stmt->error]);
    }
    
    $result = $stmt->get_result();
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'ceggid' => $row['ceggid'],
            'nweight' => $row['nweight'],
            'nlength' => $row['nlength'],
            'nwidth' => $row['nwidth'],
            'ceggcondition' => $row['ceggcondition'],
            'cincubatorid' => $row['cincubatorid'],
            'cbreedingid' => $row['cbreedingid']
        ];
    }
    
    $stmt->close();
    
    // Return success response with data
    send_json(['success' => true, 'data' => $data]);
    
} catch (Exception $e) {
    send_json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
