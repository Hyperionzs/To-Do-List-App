<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "todo_list";

// Connect to database
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Create tasks table if not exists
$sql = "CREATE TABLE IF NOT EXISTS tasks (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    task VARCHAR(255) NOT NULL,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    category VARCHAR(50) DEFAULT 'personal',
    due_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating table: " . $conn->error);
}

// Check if columns already exist - if not, add them
$result = $conn->query("SHOW COLUMNS FROM tasks LIKE 'priority'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE tasks ADD COLUMN priority ENUM('low', 'medium', 'high') DEFAULT 'medium'";
    $conn->query($sql);
}

$result = $conn->query("SHOW COLUMNS FROM tasks LIKE 'category'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE tasks ADD COLUMN category VARCHAR(50) DEFAULT 'personal'";
    $conn->query($sql);
}

$result = $conn->query("SHOW COLUMNS FROM tasks LIKE 'due_date'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE tasks ADD COLUMN due_date DATE NULL";
    $conn->query($sql);
}
?>