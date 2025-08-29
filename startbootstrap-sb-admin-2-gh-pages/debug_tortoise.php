<?php
require_once 'config/database.php';

echo "<h2>Debug Tortoise Addition</h2>";

// Test database connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $test_query = "SELECT COUNT(*) as count FROM tbltortoise";
    $stmt = $pdo->query($test_query);
    $result = $stmt->fetch();
    echo "✅ Database connection successful. Current tortoise count: " . $result['count'] . "<br>";
} catch(Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test form submission
echo "<h3>2. Form Submission Test</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "✅ Form submitted via POST<br>";
    echo "POST data received:<br>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    if (isset($_POST['add_tortoise'])) {
        echo "✅ 'add_tortoise' button detected<br>";
        
        $name = $_POST['name'] ?? '';
        $age = $_POST['age'] ?? '';
        $species = $_POST['species'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $enclosure = $_POST['enclosure'] ?? '';
        
        echo "Form values:<br>";
        echo "Name: '$name'<br>";
        echo "Age: '$age'<br>";
        echo "Species: '$species'<br>";
        echo "Gender: '$gender'<br>";
        echo "Enclosure: '$enclosure'<br>";
        
        if (empty($name) || empty($age) || empty($species) || empty($gender) || empty($enclosure)) {
            echo "❌ Validation failed: All fields are required<br>";
        } else {
            echo "✅ Validation passed<br>";
            
            // Map species names to species IDs
            $species_map = [
                'Asian Giant Tortoise' => 'S1',
                'Arakan Forest Turtle' => 'S2',
                'Elongated Tortoise' => 'S3',
                'Keeled Box Turtle' => 'S4'
            ];
            
            $species_id = $species_map[$species] ?? 'S1';
            echo "Species ID mapped to: '$species_id'<br>";
            
            // Generate new tortoise ID
            try {
                $stmt = $pdo->query("SELECT MAX(CAST(ctortoiseid AS UNSIGNED)) as max_id FROM tbltortoise");
                $result = $stmt->fetch();
                $new_id = str_pad(($result['max_id'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
                echo "Generated new ID: '$new_id'<br>";
                
                // Insert new tortoise
                $query = "INSERT INTO tbltortoise (ctortoiseid, cname, nage, cgender, cenclosureid, cspeciesid) VALUES (:id, :name, :age, :gender, :enclosure, :species)";
                $stmt = $pdo->prepare($query);
                $result = $stmt->execute([
                    ':id' => $new_id,
                    ':name' => $name,
                    ':age' => $age,
                    ':gender' => $gender,
                    ':enclosure' => $enclosure,
                    ':species' => $species_id
                ]);
                
                if ($result) {
                    echo "✅ Tortoise added successfully with ID: $new_id<br>";
                    
                    // Verify the insertion
                    $verify_stmt = $pdo->prepare("SELECT * FROM tbltortoise WHERE ctortoiseid = :id");
                    $verify_stmt->execute([':id' => $new_id]);
                    $new_tortoise = $verify_stmt->fetch();
                    
                    if ($new_tortoise) {
                        echo "✅ Verification successful. New tortoise found in database:<br>";
                        echo "<pre>";
                        print_r($new_tortoise);
                        echo "</pre>";
                    } else {
                        echo "❌ Verification failed. Tortoise not found in database after insertion.<br>";
                    }
                } else {
                    echo "❌ Insert failed<br>";
                }
                
            } catch(Exception $e) {
                echo "❌ Error during insertion: " . $e->getMessage() . "<br>";
            }
        }
    } else {
        echo "❌ 'add_tortoise' button not detected<br>";
    }
} else {
    echo "❌ No POST data received<br>";
}

// Show current tortoises
echo "<h3>3. Current Tortoises in Database</h3>";
try {
    $query = "SELECT ctortoiseid, cname, nage, cgender, cenclosureid, cspeciesid FROM tbltortoise ORDER BY ctortoiseid DESC LIMIT 10";
    $stmt = $pdo->query($query);
    $tortoises = $stmt->fetchAll();
    
    if (empty($tortoises)) {
        echo "No tortoises found in database<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
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
    }
} catch(Exception $e) {
    echo "❌ Error fetching tortoises: " . $e->getMessage() . "<br>";
}

// Show available enclosures
echo "<h3>4. Available Enclosures</h3>";
try {
    $enclosure_stmt = $pdo->query("SELECT cenclosureid, clocation, cenclosuretype FROM tblenclosure ORDER BY cenclosureid");
    $enclosures = $enclosure_stmt->fetchAll();
    
    if (empty($enclosures)) {
        echo "No enclosures found in database<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Location</th><th>Type</th></tr>";
        foreach ($enclosures as $enc) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($enc['cenclosureid']) . "</td>";
            echo "<td>" . htmlspecialchars($enc['clocation']) . "</td>";
            echo "<td>" . htmlspecialchars($enc['cenclosuretype']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch(Exception $e) {
    echo "❌ Error fetching enclosures: " . $e->getMessage() . "<br>";
}
?>

<h3>5. Test Form</h3>
<form method="POST">
    <div>
        <label for="name">Tortoise Name:</label>
        <input type="text" name="name" id="name" required>
    </div>
    <div>
        <label for="age">Age:</label>
        <input type="number" name="age" id="age" required>
    </div>
    <div>
        <label for="species">Species:</label>
        <select name="species" id="species" required>
            <option value="">Select Species</option>
            <option value="Asian Giant Tortoise">Asian Giant Tortoise</option>
            <option value="Arakan Forest Turtle">Arakan Forest Turtle</option>
            <option value="Elongated Tortoise">Elongated Tortoise</option>
            <option value="Keeled Box Turtle">Keeled Box Turtle</option>
        </select>
    </div>
    <div>
        <label for="gender">Gender:</label>
        <select name="gender" id="gender" required>
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="juvenile">Juvenile</option>
        </select>
    </div>
    <div>
        <label for="enclosure">Enclosure:</label>
        <select name="enclosure" id="enclosure" required>
            <option value="">Select Enclosure</option>
            <?php foreach ($enclosures as $enc): ?>
                <option value="<?php echo htmlspecialchars($enc['cenclosureid']); ?>">
                    <?php echo htmlspecialchars($enc['cenclosureid']); ?> - <?php echo htmlspecialchars($enc['clocation']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <button type="submit" name="add_tortoise">Add Tortoise</button>
    </div>
</form>

<p><a href="homePage.php">← Back to Homepage</a></p>
