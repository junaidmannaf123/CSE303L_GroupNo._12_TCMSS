<?php
session_start();
echo "<h2>Session Test - Clear Session Functionality</h2>";

echo "<h3>Current Session Status:</h3>";
if (isset($_SESSION['staff_id'])) {
    echo "<p style='color: green;'>✅ Session EXISTS - User is logged in</p>";
    echo "<p><strong>Staff ID:</strong> " . $_SESSION['staff_id'] . "</p>";
    echo "<p><strong>Name:</strong> " . $_SESSION['staff_name'] . "</p>";
    echo "<p><strong>Role:</strong> " . $_SESSION['role'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ NO Session - User is NOT logged in</p>";
}

echo "<h3>Actions:</h3>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
echo "<p><a href='login.php?clear=1'>Clear Session & Go to Login</a></p>";
echo "<p><a href='test_auth.php'>Test Authentication Status</a></p>";

echo "<h3>Session Information:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Cookie Information:</h3>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";
?>
