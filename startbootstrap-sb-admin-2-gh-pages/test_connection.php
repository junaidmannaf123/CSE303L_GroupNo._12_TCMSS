<?php
// Test database connection
require_once 'config/database.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test basic connection
    echo "<p>✅ Database connection successful!</p>";
    
    // Test if tables exist
    $tables = ['tbltortoise', 'tblspecies', 'tblenclosure'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Table '$table' exists</p>";
        } else {
            echo "<p>❌ Table '$table' not found</p>";
        }
    }
    
    // Test tortoise count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tbltortoise");
    $result = $stmt->fetch();
    echo "<p>📊 Total tortoises in database: " . $result['count'] . "</p>";
    
    // Show sample tortoise data
    echo "<h3>Sample Tortoise Data:</h3>";
    $stmt = $pdo->query("SELECT * FROM tbltortoise LIMIT 5");
    $tortoises = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Age</th><th>Gender</th><th>Enclosure</th><th>Species</th></tr>";
    
    foreach ($tortoises as $tortoise) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($tortoise['ctortoiseid']) . "</td>";
        echo "<td>" . htmlspecialchars($tortoise['cname']) . "</td>";
        echo "<td>" . htmlspecialchars($tortoise['nage']) . "</td>";
        echo "<td>" . htmlspecialchars($tortoise['cgender']) . "</td>";
        echo "<td>" . htmlspecialchars($tortoise['cenclosureid']) . "</td>";
        echo "<td>" . htmlspecialchars($tortoise['cspeciesid']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch(PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
} catch(Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin-top: 10px; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
