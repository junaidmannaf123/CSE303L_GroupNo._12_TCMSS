<?php
session_start();
echo "<h2>Session Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Authentication Status</h2>";
if (isset($_SESSION['staff_id'])) {
    echo "<p style='color: green;'>✅ User is logged in</p>";
    echo "<p><strong>Staff ID:</strong> " . $_SESSION['staff_id'] . "</p>";
    echo "<p><strong>Name:</strong> " . $_SESSION['staff_name'] . "</p>";
    echo "<p><strong>Role:</strong> " . $_SESSION['role'] . "</p>";
    echo "<p><strong>Login Time:</strong> " . date('Y-m-d H:i:s', $_SESSION['login_time']) . "</p>";
    
    echo "<h3>Actions</h3>";
    echo "<p><a href='logout.php'>Logout</a></p>";
    echo "<p><a href='caretaker_dashboard.php'>Go to Caretaker Dashboard</a></p>";
    echo "<p><a href='feeding.php'>Go to Feeding Page</a></p>";
} else {
    echo "<p style='color: red;'>❌ User is NOT logged in</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
}

echo "<h2>Cookie Information</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";
?>
