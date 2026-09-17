<?php
require_once __DIR__ . '/includes/db_connect.php';
$requiredRole = 'employee';
require_once __DIR__ . '/includes/auth_guard.php';

$pageTitle = 'My Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['employee_name']); ?></h1>
        <p>Your attendance and leave request tools will appear here</p>
    </div>
</div>

<div class="empty-state">
    My Attendance and My Leave Requests pages are coming next — this is a placeholder so your login flow works end-to-end in the meantime.
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>