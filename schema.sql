--------------------------------------------------------------------------
-- CREATE DATABASE AND TABLES IF NOT EXISTS
-- ALL TABLES ARE CREATED WITH APPROPRIATE CONSTRAINTS AND DATA TYPES
-- THIS SCRIPT IS SAFE TO RUN MULTIPLE TIMES EVEN IF THE DATABASE AND TABLES ALREADY EXIST
--------------------------------------------------------------------------

CREATE DATABASE IF NOT EXISTS 'hrms'; -- Create the database if it doesn't exist

--------------------------------------------------------------------------
-- USERS TABLE 1:MANY RELATIONSHIP WITH EMPLOYEES TABLE
--------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS users ( 
	user_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(120) NOT NULL,
    `role` VARCHAR(20) NOT NULL
);

--------------------------------------------------------------------------
-- Add foreign key constraint to users table referencing employees table
--------------------------------------------------------------------------

ALTER TABLE users ADD CONSTRAINT fk_emp_id FOREIGN KEY (employee_id) REFERENCES employees(employee_id); 

--------------------------------------------------------------------------
-- EMPLOYEES TABLE 1:MANY RELATIONSHIP WITH ATTENDANCE AND LEAVE_REQUEST TABLES
--------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS employees (
	employee_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(13) NOT NULL UNIQUE,
    `address` VARCHAR(120),
    date_hired DATE NOT NULL,
    department VARCHAR(30) NOT NULL,
    `position` VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--------------------------------------------------------------------------
-- ATTENDANCE TABLE 1:MANY RELATIONSHIP WITH EMPLOYEES TABLE
--------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance (
	attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    log_date DATE NOT NULL DEFAULT (CURDATE()),
    time_in TIME NULL,
    time_out TIME NULL,
    status ENUM('PRESENT', 'LATE', 'ON LEAVE') NOT NULL DEFAULT 'PRESENT',
    CONSTRAINT fk_empID_att FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
); 

--------------------------------------------------------------------------
-- LEAVE_REQUEST TABLE 1:MANY RELATIONSHIP WITH EMPLOYEES TABLE
--------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS leave_request (
	leave_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    leave_type VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason VARCHAR(120),
    `status` ENUM('PENDING', 'ACCEPTED', 'REJECTED') NULL,
    date_filed DATE NOT NULL,
    CONSTRAINT fk_empID_lr FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);

--------------------------------------------------------------------------
-- SAMPLE DATA INSERTION
--------------------------------------------------------------------------

INSERT INTO employees (first_name, last_name, email, phone, address, date_hired, department, position) VALUES
('Maria', 'Santos', 'maria.santos@example.com', '09171234567', 'Tarlac City', '2023-03-15', 'Human Resources', 'HR Officer'),
('Juan', 'Dela Cruz', 'juan.delacruz@example.com', '09181234567', 'San Isidro, Tarlac', '2022-11-01', 'IT', 'Systems Administrator'),
('Ana', 'Reyes', 'ana.reyes@example.com', '09191234567', 'Capas, Tarlac', '2024-01-10', 'Finance', 'Accounting Clerk'),
('Mark', 'Villanueva', 'mark.villanueva@example.com', '09201234567', 'Concepcion, Tarlac', '2024-06-20', 'IT', 'Web Developer');