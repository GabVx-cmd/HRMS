-- ============================================================
-- HRMS Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS hrms;
USE hrms;

-- ------------------------------------------------------------
-- EMPLOYEES (created first — users references it)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employees (
    employee_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name  VARCHAR(50) NOT NULL,
    last_name   VARCHAR(50) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    phone       VARCHAR(13) NOT NULL UNIQUE,
    address     VARCHAR(120),
    date_hired  DATE NOT NULL,
    department  VARCHAR(30) NOT NULL,
    position    VARCHAR(20),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- USERS (login accounts for admin/hr; employee_id links back)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id     INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    username    VARCHAR(50) NOT NULL UNIQUE,
    password    VARCHAR(120) NOT NULL,
    role        VARCHAR(20) NOT NULL,
    CONSTRAINT fk_emp_id FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);

-- ------------------------------------------------------------
-- ATTENDANCE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id   INT NOT NULL,
    log_date      DATE NOT NULL DEFAULT (CURDATE()),
    time_in       TIME NULL,
    time_out      TIME NULL,
    status        ENUM('PRESENT', 'LATE', 'ON LEAVE') NOT NULL DEFAULT 'PRESENT',
    CONSTRAINT fk_empID_att FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);

-- ------------------------------------------------------------
-- LEAVE_REQUEST
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS leave_request (
    leave_id    INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    leave_type  VARCHAR(50) NOT NULL,
    start_date  DATE NOT NULL,
    end_date    DATE NOT NULL,
    reason      VARCHAR(120),
    status      ENUM('PENDING', 'ACCEPTED', 'REJECTED') NULL,
    date_filed  DATE NOT NULL,
    CONSTRAINT fk_empID_lr FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);

-- ------------------------------------------------------------
-- Sample data
-- ------------------------------------------------------------
INSERT INTO employees (first_name, last_name, email, phone, address, date_hired, department, position) VALUES
('Maria', 'Santos', 'maria.santos@example.com', '09171234567', 'Tarlac City', '2023-03-15', 'Human Resources', 'HR Officer'),
('Juan', 'Dela Cruz', 'juan.delacruz@example.com', '09181234567', 'San Isidro, Tarlac', '2022-11-01', 'IT', 'Systems Administrator'),
('Ana', 'Reyes', 'ana.reyes@example.com', '09191234567', 'Capas, Tarlac', '2024-01-10', 'Finance', 'Accounting Clerk'),
('Mark', 'Villanueva', 'mark.villanueva@example.com', '09201234567', 'Concepcion, Tarlac', '2024-06-20', 'IT', 'Web Developer');

-- ------------------------------------------------------------
-- Sample admin/hr login account, linked to Maria Santos (employee_id 1)
-- Username doubles as her email for the Admin/HR login form.
-- Password stored in plain text for now, matching User::login()'s
-- current plaintext comparison (hashing is a Final Term requirement).
-- ------------------------------------------------------------
INSERT INTO users (employee_id, username, password, role) VALUES
(1, 'maria.santos@example.com', 'password123', 'hr');