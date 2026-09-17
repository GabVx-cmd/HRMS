<?php
/**
 * Include this AFTER db_connect.php (session must already be started)
 * on any page that requires the user to be logged in.
 *
 * Usage:
 *   require_once __DIR__ . '/includes/db_connect.php';
 *   $requiredRole = 'admin_hr'; // or 'employee' — omit to allow either
 *   require_once __DIR__ . '/includes/auth_guard.php';
 */
if (empty($_SESSION['user_type'])) {
    header('Location: login.php');
    exit;
}

if (isset($requiredRole) && $_SESSION['user_type'] !== $requiredRole) {
    header('Location: login.php?error=unauthorized');
    exit;
}