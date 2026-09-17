<?php
/**
 * Dashboard
 * Pulls all employee records once through the existing Employee class,
 * then computes summary stats in plain PHP. No new backend methods
 * were added — this reuses Employee::getAllEmployees() exactly as
 * your teammate wrote it.
 */
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/db_connect.php';
$requiredRole = 'admin_hr';
require_once __DIR__ . '/includes/auth_guard.php';

// Empty search/department = no filtering. High limit so we effectively
// get every record for the stats below (fine for a school-project dataset).
$allEmployees = $employee->getAllEmployees('', '', 1000, 0);

$totalEmployees = count($allEmployees);

// Group employees by department
$byDepartment = [];
foreach ($allEmployees as $emp) {
    $dept = !empty($emp['department']) ? $emp['department'] : 'Unassigned';
    if (!isset($byDepartment[$dept])) {
        $byDepartment[$dept] = 0;
    }
    $byDepartment[$dept]++;
}
arsort($byDepartment); // largest department first

// Recently hired: sort by date_hired descending, take the top 5
$recentHires = $allEmployees;
usort($recentHires, function ($a, $b) {
    return strtotime($b['date_hired']) <=> strtotime($a['date_hired']);
});
$recentHires = array_slice($recentHires, 0, 5);

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Overview of current employee records</p>
    </div>
    <a href="employee_form.php" class="btn btn-primary">Add employee</a>
</div>

<div class="stat-row">
    <div class="stat">
        <span class="value"><?php echo $totalEmployees; ?></span>
        <span class="label">Total employees</span>
    </div>
    <div class="stat">
        <span class="value"><?php echo count($byDepartment); ?></span>
        <span class="label">Departments</span>
    </div>
</div>

<div class="dashboard-columns">

    <section>
        <h2>Employees by department</h2>
        <?php if (empty($byDepartment)): ?>
            <div class="empty-state">No employee records yet. Add your first employee to get started.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Department</th><th>Employees</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($byDepartment as $dept => $count): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dept); ?></td>
                            <td><?php echo $count; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <h2>Recently hired</h2>
        <?php if (empty($recentHires)): ?>
            <div class="empty-state">No employee records yet.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Name</th><th>Position</th><th>Date hired</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recentHires as $emp): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($emp['position']); ?></td>
                            <td><?php echo htmlspecialchars($emp['date_hired']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>