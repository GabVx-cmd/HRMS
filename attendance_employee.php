<?php
/**
 * Employee Attendance page.
 * Lets the logged-in employee clock themselves in/out, view their own
 * history, and see a simple monthly stats summary.
 */
require_once __DIR__ . '/includes/db_connect.php';
$requiredRole = 'employee';
require_once __DIR__ . '/includes/auth_guard.php';

$employeeId = (int)$_SESSION['employee_id'];
$actionMessage = null;
$actionError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'clock_in') {
        $ok = $attendance->clockIn($employeeId);
        $actionMessage = $ok ? 'Clocked in successfully.' : null;
        $actionError = $ok ? null : 'You have already clocked in today.';
    } elseif ($action === 'clock_out') {
        $ok = $attendance->clockOut($employeeId);
        $actionMessage = $ok ? 'Clocked out successfully.' : null;
        $actionError = $ok ? null : 'You need to clock in first before clocking out.';
    }
}

$hasClockedInToday = $attendance->hasClockedIn($employeeId);

// Last 30 days of history
$startDate = (new DateTime())->modify('-30 days')->format('Y-m-d');
$endDate = date('Y-m-d');
$history = $attendance->getEmployeeAttendance($employeeId, $startDate, $endDate);

$stats = $attendance->attendanceStats($employeeId, $startDate, $endDate);

$pageTitle = 'My Attendance';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>My Attendance</h1>
        <p>Clock in/out and review your attendance history</p>
    </div>
</div>

<?php if ($actionMessage): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($actionMessage); ?></div>
<?php elseif ($actionError): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($actionError); ?></div>
<?php endif; ?>

<form method="post" style="margin-bottom: 24px;">
    <?php if (!$hasClockedInToday): ?>
        <button type="submit" name="action" value="clock_in" class="btn btn-primary">Clock in</button>
    <?php else: ?>
        <button type="submit" name="action" value="clock_out" class="btn btn-secondary">Clock out</button>
    <?php endif; ?>
</form>

<div class="stat-row">
    <div class="stat">
        <span class="value"><?php echo $stats['days_present'] ?? 0; ?></span>
        <span class="label">Days present (last 30 days)</span>
    </div>
    <div class="stat">
        <span class="value"><?php echo $stats['days_late'] ?? 0; ?></span>
        <span class="label">Days late</span>
    </div>
</div>

<h2>Recent history</h2>
<?php if (empty($history)): ?>
    <div class="empty-state">No attendance records yet in the last 30 days.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time in</th>
                <th>Time out</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['log_date']); ?></td>
                    <td><?php echo htmlspecialchars($r['time_in'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($r['time_out'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($r['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>