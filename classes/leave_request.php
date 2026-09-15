<?php
require_once 'user.php';
/**
 * Class LeaveRequest
 * This class handles leave request operations for employees.
 * It extends the User class to inherit user-related functionalities.
 */
class LeaveRequest extends User{
    private string $leaveTable = 'leave_request';

    /**
     * Constructor for the LeaveRequest class
     * Inherits from the User class and initializes the database connection
     */
    public function __construct(PDO $db) {
        try {
            parent::__construct($db);
        } catch (Throwable $e) {
            error_log("Leave request initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Creates a new leave request in the database.
     * @param int $employee_id The ID of the employee filing the leave request.
     * @param string $reason The reason for the leave request.
     * @param string $leaveType The type of leave being requested (e.g., 'SICK_LEAVE', 'VACATION_LEAVE', etc.).
     * @param string $startDate The start date of the leave request in 'YYYY-MM-DD' format.
     * @param string $endDate The end date of the leave request in 'YYYY-MM-DD' format.
     * @return bool The result of the insert operation. Returns true if the leave request was successfully filed, false otherwise.
     */
    public function fileLeave(int $employee_id, string $reason, string $leaveType, string $startDate, string $endDate):bool {
        try {
            $leaveType = strtoupper($leaveType);
            $query = "INSERT INTO " .$this->leaveTable . " (employee_id, reason, leave_type, start_date, end_date, status, date_filed)
                VALUES (:employee_id, :reason, :leave_type, :start_date, :end_date, 'PENDING', CURDATE())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(':leave_type', $leaveType, PDO::PARAM_STR);
            $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
            $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
            $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (Throwable $e) {
            error_log("Leave request filing error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves leave requests of employee in the database.
     * @param int $employee_id The ID of the employee whose leave requests are to be retrieved.
     * @param string|null $status The status to filter leave requests by. It can be 'PENDING', 'APPROVED', or 'REJECTED'. If null, all leave requests for the employee are retrieved.
     * @return array The leave requests of the specified employee, or an empty array if no leave requests are found.
     */
    public function getLeaveRequests(int $employee_id, ?string $status): array {
        try {
            $query = "SELECT * FROM " . $this->leaveTable . " WHERE employee_id = :employee_id";
            if(!empty($status)) {
                $query .= " AND status = :status";
            }
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            if(!empty($status)) {
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            }
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Leave request lookup error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieves all leave requests from the database, optionally filtered by status.
     * @param string|null $status The status to filter leave requests by. It can be 'PENDING', 'APPROVED', or 'REJECTED'. If null, all leave requests are retrieved.
     * @return array An array of leave requests matching the specified status, or all leave requests if no status is provided.
     */
    public function getAllLeaveRequests(?string $status = null): array {    
        try {
            $query = "SELECT * FROM " . $this->leaveTable;
            match($status) {
                "PENDING" => $query .= " WHERE status = 'PENDING'",
                "APPROVED" => $query .= " WHERE status = 'APPROVED'",
                "REJECTED" => $query .= " WHERE status = 'REJECTED'",
                default => ""
            };
            $query .= " ORDER BY date_filed ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Leave request list error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates the leave request status of employee
     * @param int $employee_id The ID of the employee whose leave request status is to be updated.
     * @param string $status The new status to be set for the leave request. It can be 'PENDING', 'APPROVED', or 'REJECTED'.
     * @return bool The result of the update operation. Returns true if the status was successfully updated, false otherwise.
     */
    public function updateLeaveStatus(int $employee_id, string $status): bool {
        try {
            $query = "UPDATE ". $this->leaveTable . " SET status = :status WHERE employee_id = :employee_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":employee_id", $employee_id, PDO::PARAM_INT);
            $stmt->bindParam(":status", $status, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (Throwable $e) {
            error_log("Leave request status update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a leave request
     * @param int $employee_id
     * @return bool The result of the delete operation. Returns true if the leave request was successfully deleted, false otherwise.
     * Disabled for now to prevent accidental deletion of leave requests. If you want to enable this functionality, uncomment the method below.
     */
    
    /*public function deleteLeaveRequest(int $employee_id): bool {
        $query = "DELETE FROM". $this->leaveTable . " WHERE employee_id = :employee_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":employee_id", $employee_id, PDO::PARAM_INT);
        return $stmt->execute();
    }*/
}