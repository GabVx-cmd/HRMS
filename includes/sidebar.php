<?php
/**
 * Sidebar navigation.
 * Nav items shown depend on $_SESSION['user_type'] (set at login).
 * Attendance and Leave Request nav items will be added here once
 * those pages exist — for now, employee-role users just see their
 * name and a logout link.
 */
$currentPage = basename($_SERVER['PHP_SELF']);

if (($_SESSION['user_type'] ?? null) === 'admin_hr') {
    $navItems = [
        'index.php'            => 'Dashboard',
        'employees.php'        => 'Employees',
        'attendance_admin.php' => 'Attendance',
        'leave_admin.php'      => 'Leave Requests',
        'create_account.php'   => 'Create Account',
    ];
} else {
    $navItems = [
        'employee_dashboard.php'  => 'Dashboard',
        'attendance_employee.php' => 'My Attendance',
        'leave_employee.php'      => 'My Leave Requests',
    ];
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="mark">HRMS</span>
        <span class="sub">Employee Records</span>
    </div>
    <ul class="sidebar-nav">
        <?php foreach ($navItems as $file => $label): ?>
            <li>
                <a href="<?php echo $file; ?>"
                   class="<?php echo $currentPage === $file ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($label); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (!empty($_SESSION['user_type'])): ?>
        <div class="sidebar-user">
            <?php if ($_SESSION['user_type'] === 'admin_hr'): ?>
                <div class="name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                <div class="role"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
            <?php else: ?>
                <div class="name"><?php echo htmlspecialchars($_SESSION['employee_name']); ?></div>
                <div class="role">Employee</div>
            <?php endif; ?>
            <a href="logout.php">Log out</a>
        </div>
    <?php endif; ?>
</aside>