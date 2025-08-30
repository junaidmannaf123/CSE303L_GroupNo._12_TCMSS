<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $response = [
        'success' => true,
        'data' => []
    ];

    // Get total eggs from breeding records
    $stmt = $mysqli->prepare("SELECT SUM(neggscount) as total_eggs FROM tblbreedingrecord WHERE neggscount IS NOT NULL AND neggscount > 0");
    $stmt->execute();
    $result = $stmt->get_result();
    $total_eggs = $result->fetch_assoc()['total_eggs'] ?? 0;
    $response['data']['totalEggs'] = $total_eggs;

    // Get average hatching service rate from breeding records
    $stmt = $mysqli->prepare("SELECT AVG(nhatchingservicerate) as avg_hatch_rate FROM tblbreedingrecord WHERE nhatchingservicerate IS NOT NULL AND nhatchingservicerate > 0");
    $stmt->execute();
    $result = $stmt->get_result();
    $avg_hatch_rate = $result->fetch_assoc()['avg_hatch_rate'] ?? 0;
    $response['data']['avgHatchRate'] = round($avg_hatch_rate, 1);

    // Get egg condition vs average weight data
    $stmt = $mysqli->prepare("
        SELECT 
            ceggcondition,
            AVG(nweight) as avg_weight,
            COUNT(*) as count
        FROM tbleggdetails 
        WHERE ceggcondition IS NOT NULL AND ceggcondition != '' AND nweight IS NOT NULL AND nweight > 0
        GROUP BY ceggcondition 
        ORDER BY avg_weight DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $condition_weights = [];
    while ($row = $result->fetch_assoc()) {
        $condition_weights[] = [
            'condition' => $row['ceggcondition'],
            'avgWeight' => round($row['avg_weight'], 1),
            'count' => $row['count']
        ];
    }
    $response['data']['conditionWeights'] = $condition_weights;

    // Get hatching service rate distribution for pie chart
    $stmt = $mysqli->prepare("
        SELECT 
            CASE 
                WHEN nhatchingservicerate >= 80 THEN 'Excellent (80-100%)'
                WHEN nhatchingservicerate >= 70 THEN 'Good (70-79%)'
                WHEN nhatchingservicerate >= 60 THEN 'Fair (60-69%)'
                WHEN nhatchingservicerate >= 50 THEN 'Poor (50-59%)'
                ELSE 'Very Poor (<50%)'
            END as rate_category,
            COUNT(*) as count
        FROM tblbreedingrecord 
        WHERE nhatchingservicerate IS NOT NULL AND nhatchingservicerate > 0
        GROUP BY rate_category
        ORDER BY 
            CASE rate_category
                WHEN 'Excellent (80-100%)' THEN 1
                WHEN 'Good (70-79%)' THEN 2
                WHEN 'Fair (60-69%)' THEN 3
                WHEN 'Poor (50-59%)' THEN 4
                WHEN 'Very Poor (<50%)' THEN 5
            END
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $hatch_rate_distribution = [];
    while ($row = $result->fetch_assoc()) {
        $hatch_rate_distribution[] = [
            'category' => $row['rate_category'],
            'count' => $row['count']
        ];
    }
    $response['data']['hatchRateDistribution'] = $hatch_rate_distribution;

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
