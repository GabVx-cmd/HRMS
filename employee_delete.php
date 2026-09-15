<?php
/**
 * Deletes the employee given in ?id=, then redirects back to the list.
 * The confirmation dialog happens client-side (in employees.php) before
 * this URL is ever hit, so no confirmation UI is needed here.
 */
require_once __DIR__ . '/includes/db_connect.php';

$employeeId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($employeeId) {
    $employee->deleteEmployee($employeeId);
}

header('Location: employees.php?flash=deleted');
exit;