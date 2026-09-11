<?php
/**
 * Test database connection
 * This script tests the connection to the database using the db class
 */
require('db.php');

$db = new db(); // Create an instance of the db class
    
$conn = $db->getConnection();

$sql = 'SELECT 1';

if ($conn->query($sql)){
    echo 'Database connection successful!'; // Output a success message if the connection is successful
} else {
    echo 'Database connection failed: ' . $conn->errorInfo(); // Output an error message if the connection fails
}