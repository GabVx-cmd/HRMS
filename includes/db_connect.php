<?php
/**
 * Shared bootstrap: opens the DB connection once and instantiates
 * the Employee model, so pages just include this file instead of
 * repeating setup on every page.
 *
 * Confirmed via testdb.php: the db class exposes getConnection(),
 * which returns a PDO instance.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/user.php';
require_once __DIR__ . '/../classes/employee.php';

$database = new db();
$conn = $database->getConnection();

$employee = new Employee($conn);