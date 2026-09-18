<?php
/**
 * Shared bootstrap: starts the session, opens the DB connection once,
 * and instantiates the User and Employee models, so pages just include
 * this file instead of repeating setup on every page.
 *
 * Confirmed via testdb.php: the db class exposes getConnection(),
 * which returns a PDO instance.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/user.php';
require_once __DIR__ . '/../classes/employee.php';
require_once __DIR__ . '/../classes/attendance.php';
require_once __DIR__ . '/../classes/leave_request.php';
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$database = new db();
$conn = $database->getConnection();

$user = new User($conn);
$employee = new Employee($conn);
$attendance = new Attendance($conn);
$leaveRequest = new LeaveRequest($conn);