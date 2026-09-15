-- ============================================================
-- HRMS Database Schema
-- Generated from the ERD / Data Dictionary in the project
-- documentation report (Section E).
-- Safe to run even if the database doesn't exist yet.
-- ============================================================

CREATE DATABASE IF NOT EXISTS hrms;
USE hrms;

-- ------------------------------------------------------------
-- EMPLOYEES (central table)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(50)  NOT NULL,
    last_name   VARCHAR(50)  NOT NULL,
    email       VARCHAR(100) UNIQUE,
    phone       VARCHAR(20),
    address     VARCHAR(150),
    date_hired  DATE,
    department  VARCHAR(50),
    position    VARCHAR(50),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- USERS (1:1 with employees — login credentials + role)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    username    VARCHAR(50) UNIQUE NOT NULL,
    password    VARCHAR(50) NOT NULL,
    role        VARCHAR(50) NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);

-- ------------------------------------------------------------
-- ATTENDANCE (1:many with employees)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id   INT NOT NULL,
    log_date      DATE NOT NULL,
    time_in       TIME,
    time_out      TIME,
    status        VARCHAR(20),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);

-- ------------------------------------------------------------
-- LEAVE_REQUEST (1:many with employees)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS leave_request (
    leave_id    INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type  VARCHAR(30),
    start_date  DATE NOT NULL,
    end_date    DATE NOT NULL,
    reason      TEXT,
    status      VARCHAR(20),
    date_filed  DATE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);

-- ------------------------------------------------------------
-- Sample data so the Dashboard/Employees list have something
-- to display on first test. Safe to delete once you have real data.
-- ------------------------------------------------------------
INSERT INTO employees (first_name, last_name, email, phone, address, date_hired, department, position) VALUES
('Maria', 'Santos', 'maria.santos@example.com', '09171234567', 'Tarlac City', '2023-03-15', 'Human Resources', 'HR Officer'),
('Juan', 'Dela Cruz', 'juan.delacruz@example.com', '09181234567', 'San Isidro, Tarlac', '2022-11-01', 'IT', 'Systems Administrator'),
('Ana', 'Reyes', 'ana.reyes@example.com', '09191234567', 'Capas, Tarlac', '2024-01-10', 'Finance', 'Accounting Clerk'),
('Mark', 'Villanueva', 'mark.villanueva@example.com', '09201234567', 'Concepcion, Tarlac', '2024-06-20', 'IT', 'Web Developer');