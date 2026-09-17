<?php
/**
 * Attendance class
 * Manages attendance records for the HRMS
 * Includes methods for recording attendance, retrieving attendance records, and calculating attendance statistics
 */
require_once('user.php');
class Attendance extends User{
    private string $attTable = 'attendance';

    /**
     * Constructor for the Attendance class
     * Inherits from the User class and initializes the database connection
     */
    public function __construct(PDO $db) {
        try {
            parent::__construct($db);
        } catch (Throwable $e) {
            error_log('Attendance initialization error: ' . $e->getMessage());
        }
    }

    /**
     * Records the clock-in time for an employee for the current date.
     * @param int $employee_id The ID of the employee
     * @param string|null $timeIn The clock-in time (optional). Defaults to current time if not provided.
     * @return bool Returns true if the clock-in was successful, false otherwise.
     * @throws Exception If the employee has already clocked in today.
     */
    public function clockIn(int $employee_id, ?string $timeIn = null): bool {
        try {
            $logDate = date('Y-m-d');
            $timeIn = $timeIn ?? date('H:i:s');
            if($this->hasClockedIn($employee_id, $logDate)){
                throw new Exception('Employee has already clocked in today.');
            }
        } catch (Throwable $e) {
            error_log("Error during clock-in: " . $e->getMessage());
            return false;
        }

        $CUTOFFTIME = date('H:i:s', strtotime('08:00:00'));
        $status = (strtotime($timeIn) > strtotime($CUTOFFTIME)) ? 'LATE' : 'PRESENT';
        
        try {
            $query = "INSERT INTO " . $this->attTable . " (employee_id, log_date, time_in, status) 
            VALUES (:employee_id, :log_date, :time_in, :status)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':log_date', $logDate, PDO::PARAM_STR);
            $stmt->bindParam(':time_in', $timeIn, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    
            return $stmt->execute();
        } catch (Throwable $e) {
            error_log("Error determining attendance status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Records the clock-out time for an employee for the current date.
     * @param int $employee_id The ID of the employee
     * @param string|null $timeOut The clock-out time (optional). Defaults to current time if not provided.
     * @return bool Returns true if the clock-out was successful, false otherwise.
     * @throws Exception If the employee has not clocked in today.
     */
    public function clockOut(int $employee_id, ?string $timeOut = null): bool {
        try {
            $logDate = date('Y-m-d');
            if(!$this->hasClockedIn($employee_id, $logDate)){
                throw new Exception('Employee has not clocked in today.');
            }
            $timeOut = $timeOut ?? date('H:i:s');
    
            $query = "UPDATE " . $this->attTable . " SET time_out = :time_out
            WHERE employee_id = :employee_id AND log_date = :log_date";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':time_out', $timeOut, PDO::PARAM_STR);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':log_date', $logDate, PDO::PARAM_STR);
            $stmt->execute();
    
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            error_log("Error during clock-out: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if an employee has clocked in for a specific date.
     * @param int $employee_id The ID of the employee
     * @param string|null $logDate The date to check (optional). Defaults to today if not provided.
     * @return bool Returns true if the employee has clocked in, false otherwise.
     */
    public function hasClockedIn(int $employee_id, ?string $logDate = null): bool {
        try {
            $logDate = $logDate ?? date('Y-m-d');
            $query = 'SELECT * FROM '. $this->attTable .' WHERE employee_id = :employee_id AND log_date = :log_date';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':log_date', $logDate, PDO::PARAM_STR);
            $stmt->execute();
    
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            error_log("Error checking clock-in status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves attendance records for a specific employee within a date range.
     * @param int $employee_id The ID of the employee
     * @param string|null $startDate The start date for the records (optional). Defaults to today if not provided.
     * @param string|null $endDate The end date for the records (optional). Defaults to today if not provided.
     * @return array Returns an array of attendance records for the specified employee and date range
     */
    public function getEmployeeAttendance(int $employee_id, ?string $startDate = null, ?string $endDate = null): array {
        try {
            $startDate = $startDate ?? date('Y-m-d');
            $endDate = $endDate ?? date('Y-m-d');
            $query = "SELECT * FROM " . $this->attTable . " 
            WHERE employee_id = :employee_id AND log_date BETWEEN :start_date AND :end_date ORDER BY log_date ASC, time_in DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
            $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
            $stmt->execute();
    
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Error fetching employee attendance: " . $e->getMessage());
            return [];
        }
    }

    /** 
     * Get all attendance records for a specific date.
     * @param string $logDate The date for which to retrieve attendance records in 'YYYY-MM-DD' format.
     * @return array Returns the attendance records for the specified date, including employee details.
     */
    public function getAllAttendance(string $logDate): array {
        try {
            $query = "SELECT a.*, e.first_name, e.last_name, e.position, e.email, e.department FROM " . $this->attTable . " a 
            INNER JOIN employees e ON a.employee_id = e.employee_id";
    
            if (!empty($logDate)) {
                $query .= " WHERE log_date = :log_date";
            }
    
            $query .= " ORDER BY log_date ASC, time_in DESC";
    
            $stmt = $this->conn->prepare($query);
    
            if(!empty($logDate)) {
                $stmt->bindParam(':log_date', $logDate, PDO::PARAM_STR);
            }
    
            $stmt->execute();
    
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Error fetching attendance records: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieves attendance statistics for a specific employee within a date range.
     * @param int $employee_id The ID of the employee
     * @param string|null $startDate The start date for the statistics (optional). Defaults to one month ago if not provided.
     * @param string|null $endDate The end date for the statistics (optional). Defaults to today if not provided.
     * @return array Returns an associative array containing attendance statistics for the specified employee and date range
     */
    public function attendanceStats(int $employee_id, ?string $startDate, ?string $endDate): array {
        try {
            $endDate = $endDate ?? date('Y-m-d');
            $startDate = $startDate ?? ((new DateTime())->modify('-1 month')->format('Y-m-d'));
            $query = "SELECT 
                        employee_id,
                        COUNT(*) as total_attendance,
                        SUM(CASE WHEN status = 'PRESENT' THEN 1 ELSE 0 END) as days_present,
                        SUM(CASE WHEN status = 'LATE' THEN 1 ELSE 0 END) as days_late,
                        SUM(CASE WHEN status = 'ON LEAVE' THEN 1 ELSE 0 END) as days_on_leave,
                        SUM(CASE WHEN time_out IS NULL THEN 1 ELSE 0 END) as days_absent
                    FROM " . $this->attTable . "
                    WHERE employee_id = :employee_id AND log_date BETWEEN :start_date AND :end_date
                    GROUP BY employee_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
            $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log("Error fetching attendance stats: " . $e->getMessage());
            return [];
        }
    }
}