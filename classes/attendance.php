<?php
// This class represents an attendance record for an employee, including attributes such as attendance ID, employee ID, log date, time in, time out, and status.
// It includes methods for logging time in and time out, as well as retrieving attendance records by employee ID.
class Attendance {
    private $attendanceId;
    private $employeeId;
    private $logDate;
    private $timeIn;
    private $timeOut;
    private $status;

    public function __construct(int $attendanceId, int $employeeId, string $logDate, string $timeIn, string $timeOut, string $status) {
        $this->attendanceId = $attendanceId;
        $this->employeeId = $employeeId;
        $this->logDate = $logDate;
        $this->timeIn = $timeIn;
        $this->timeOut = $timeOut;
        $this->status = $status;
    }

    // Function to log the time in for an employee's attendance record.
    public function logTimeIn($conn):bool {
        $sql = "INSERT INTO attendance (
            employee_id,
            log_date,
            time_in,
            time_out,
            `status`
        ) VALUES (?, ?, ?, ?)";
        try {
            $stmt = mysqli_prepare($conn, $sql);
            if(!$stmt){
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param(
                $stmt,
                "isss",
                $this->employeeId,
                $this->logDate,
                $this->timeIn,
                $this->status
            );
            if(!mysqli_stmt_execute($stmt)){
                throw new Exception(mysqli_error($conn));
            }
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        } catch(Exception $e) {
            error_log("Error logging time in: " . $e->getMessage());    
            return false;
        }
    }

    // Function to log the time out for an employee's attendance record.
    public function logTimeOut($conn):bool {
        $sql = "UPDATE attendance SET
            time_out = ?
        WHERE employee_id = ? AND log_date = ?";
        try {
            $stmt = mysqli_prepare($conn, $sql);
            if(!$stmt){
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param(
                $stmt,
                "sis",
                $this->timeOut,
                $this->employeeId,
                $this->logDate
            );
            if(!mysqli_stmt_execute($stmt)){
                throw new Exception(mysqli_error($conn));
            }
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        } catch(Exception $e) {
            error_log("Error logging time out: " . $e->getMessage());
            return false;
        }
    }

    // Function to retrieve attendance records for a specific employee by their employee ID.
    // This function does not require an instance of the Attendance class to be called, hence it is declared as static.
    public static function getByEmployeeId($conn, int $employeeId):?array {
        $sql = "SELECT * FROM attendance WHERE employee_id = ?";
        try {
            $stmt = mysqli_prepare($conn, $sql);
            if(!$stmt){
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, "i", $employeeId);
            if(!mysqli_stmt_execute($stmt)){
                throw new Exception(mysqli_error($conn));
            }
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            $attendanceRecords = [];
            while($row = mysqli_fetch_assoc($result)){
                $attendanceRecords[] = new Attendance(
                    $row['attendance_id'],
                    $row['employee_id'],
                    $row['log_date'],
                    $row['time_in'],
                    $row['time_out'],
                    $row['status']
                );
            }
            return $attendanceRecords ?: null;
        } catch (Exception $e){
            error_log("Error retrieving attendance records: " . $e->getMessage());
            return null;
        }
    }
}