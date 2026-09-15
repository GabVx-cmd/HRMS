<?php
/**
 * Sidebar navigation.
 * Highlights the active link by comparing against the current filename.
 */
$currentPage = basename($_SERVER['PHP_SELF']);

$navItems = [
    'index.php'     => 'Dashboard',
    'employees.php' => 'Employees',
];
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
</aside>