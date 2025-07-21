<?php
$host = 'localhost';
$dbname = 'evently_db';
$username = 'root';
$password = ''; // This is often an empty string for XAMPP/WAMP root user

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage()); // Keep this line for now!
}

/*
ALTER TABLE users
ADD COLUMN full_name VARCHAR(255) DEFAULT NULL,
ADD COLUMN bio TEXT DEFAULT NULL,
ADD COLUMN profile_picture VARCHAR(255) DEFAULT 'default.jpg';
*/

?>