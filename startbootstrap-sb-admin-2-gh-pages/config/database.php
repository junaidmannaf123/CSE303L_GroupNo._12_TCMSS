<?php
// Database configuration for TCMSS (Tortoise Conservation Management System)
$host = 'localhost';
$dbname = 'tccms';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Also create mysqli connection for backward compatibility
    $mysqli = new mysqli($host, $username, $password, $dbname);
    if ($mysqli->connect_error) {
        throw new Exception("MySQLi connection failed: " . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8mb4");
} catch(Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function for JSON responses
function send_json($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}