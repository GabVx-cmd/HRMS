<?php 
// This script is used to create the necessary tables for the HR management system in the 'hrdb' database.
require_once 'configdb.php';

// Function to create the 'employees' table
function employeesTable($conn) {
    $sql = "CREATE TABLE employees (
        employee_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        department VARCHAR(50) NOT NULL,
        position VARCHAR(50) NOT NULL,
        date_hired DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    try {
        $result = mysqli_query($conn, $sql);
        if (!$result){
            echo "Error creating table: ";
            throw new Exception(mysqli_error($conn));
        }
        echo "Table 'employees' created successfully \n";
    } catch (Exception $e) {
        echo "". $e->getMessage() ."";
    }
}

// Function to create the 'attendance' table
function attendanceTable($conn) {
    $sql = "CREATE TABLE attendance (
        attendance_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        employee_id INT(11) NOT NULL,
        log_date DATE NOT NULL,
        time_in TIME NULL,
        time_out TIME NULL,
        status ENUM('Present', 'Absent', 'On Leave') NOT NULL DEFAULT 'Present'
    )";

    try {
        $result = mysqli_query($conn, $sql);
        if (!$result){
            echo "Error creating table: ";
            throw new Exception(mysqli_error($conn));
        }
        echo "Table 'attendance' created successfully \n";
    } catch (Exception $e) {
        echo "". $e->getMessage() ."";
    }
}

// Function to create the 'leave_request' table
function leaveTable($conn) {
    $sql = "CREATE TABLE leave_request (
        leave_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        employee_id INT(11) NOT NULL,
        leave_type VARCHAR(50) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        reason TEXT NOT NULL,
        status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    try {
        $result = mysqli_query($conn, $sql);
        if (!$result){
            echo "Error creating table: ";
            throw new Exception(mysqli_error($conn));
        }
        echo "Table 'leave_request' created successfully \n";
    } catch (Exception $e) {
        echo "". $e->getMessage() ."";
    }
}

employeesTable($conn);
attendanceTable($conn);
leaveTable($conn);