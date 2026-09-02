<?php
// This class represents a leave request made by an employee, including attributes such as leave ID, employee ID, leave type, start date, end date, reason for leave, and status of the request.
class LeaveRequest {
    private ?int $leaveId;
    private $employeeId;
    private $leaveType;
    private $startDate;
    private $endDate;
    private $reason;
    private $status;

    public function __construct( int $employeeId, string $leaveType, string $startDate, string $endDate, string $reason, string $status) {
        $this->employeeId = $employeeId;
        $this->leaveType = $leaveType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reason = $reason;
        $this->status = $status;
    }

    // Function to insert a new leave request into the database for an employee.
    public function fileLeaveRequest($conn):bool {
        $sql = "INSERT INTO leave_requests (
            employee_id, 
            leave_type, 
            `start_date`, 
            `end_date`, 
            reason, 
            `status`
        ) VALUES (?, ?, ?, ?, ?, ?)";

        try {
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param(
                $stmt, 
                "isssss", 
                $this->employeeId, 
                $this->leaveType, 
                $this->startDate, 
                $this->endDate, 
                $this->reason, 
                $this->status
            );
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (!$result) {
                throw new Exception(mysqli_error($conn));
            }
            $this->leaveId = (int) mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            return $result;
        } catch (Exception $e) {
            error_log("Error filing leave request: " . $e->getMessage());
            return false;
        }
    }
    
    // Function to update the status of an existing leave request in the database.
    public function updateStatus($conn, string $newStatus):bool {
        $this->status = $newStatus;
        $sql = "UPDATE leave_requests SET `status` = ? WHERE leave_id = ?";
        try {
            $stmt = mysqli_prepare($conn, $sql);
            if(!$stmt){
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, "si", $newStatus, $this->leaveId);
            if(!mysqli_stmt_execute($stmt)){
                throw new Exception(mysqli_error($conn));
            }
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        } catch (Exception $e) {
            error_log("Error updating leave request status: " . $e->getMessage());
            return false;
        }
    }

    public static function getByEmployeeId($conn, int $employeeId):array {
        $sql = "SELECT * FROM leave_requests WHERE employee_id = ?";
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
            $leaveRequests = [];
            while($row = mysqli_fetch_assoc($result)){
                $leaveRequests[] = $row;
            }
            return $leaveRequests;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public static function getById($conn, int $leaveId):?array {
        $sql = "SELECT * FROM leave_requests WHERE leave_id = ?";
        try {
            $stmt = mysqli_prepare($conn, $sql);
            if(!$stmt){
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, "i", $leaveId);
            if(!mysqli_stmt_execute($stmt)){
                throw new Exception(mysqli_error($conn));
            }
            $result = mysqli_stmt_get_result($stmt);
            $leaveRequest = [];
            while($row = mysqli_fetch_assoc($result)){
                $leaveRequest[] = $row;
            }
            mysqli_stmt_close($stmt);
            return $leaveRequest;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}