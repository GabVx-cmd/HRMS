<?php
// This script is used to create a new database named 'hrdb' using the connection parameters defined in 'configdb.php'.
require_once("configdb.php");

$sql = "CREATE DATABASE hrdb";

if(!mysqli_query($conn, $sql)){
    echo "Database creation failed: " . mysqli_error($conn);
} else {
    echo "Database created successfully.";
}

?>