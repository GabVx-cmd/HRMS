<?php
// This child class extends the Person class and represents an employee with additional attributes such as department, position, and date hired. 
// It includes methods for creating, updating, deleting, and retrieving employee records from the database.
class Employee extends Person {
    private $department;
    private $position;
    private $date_hired;

    public function __construct(?int $id, string $firstName, string $lastName, string $email, string $department, string $position, string $date_hired) {
        parent::__construct($id, $firstName, $lastName, $email);
        $this->department = $department;
        $this->position = $position;
        $this->date_hired = $date_hired;
    }

    // Function to create a new employee record in the database
    public function create($conn): bool{
        $sql = "INSERT INTO employees (
            first_name,
            last_name,
            email,
            department,
            position,
            date_hired
        ) VALUES (?, ?, ?, ?, ?, ?)";
        try{
            $stmt = mysqli_prepare($conn, $sql);
            if(!$stmt){
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param(
                $stmt,
                "ssssss",
                $this->firstName,
                $this->lastName,
                $this->email,
                $this->department,
                $this->position,
                $this->date_hired
            );
            if(!mysqli_stmt_execute($stmt)){
                throw new Exception(mysqli_error($conn));
            }
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            if($result){
                $this->id = (int) mysqli_insert_id($conn);
            }
            return $result;
        } catch(Exception $e) {
            error_log("Error creating employee: " . $e->getMessage());
            return false;
        }
    }

    // Function to update an existing employee record in the database
    public function update($conn): bool{
        $sql = "UPDATE employees SET
            first_name = ?,
            last_name = ?,
            email = ?,
            department = ?,
            position = ?,
            date_hired = ?
        WHERE employee_id = ?";
        try {
            $stmt = mysqli_prepare($conn, $sql);
            if(!$stmt){
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param(
                $stmt,
                "ssssssi",
                $this->firstName,
                $this->lastName,
                $this->email,
                $this->department,
                $this->position,
                $this->date_hired,
                $this->id
            );
            if(!mysqli_stmt_execute($stmt)){
                throw new Exception("No rows updated");
            }
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            if($row){
                $this->firstName = $row['first_name'];
                $this->lastName = $row['last_name'];
                $this->email = $row['email'];
                $this->department = $row['department'];
                $this->position = $row['position'];
                $this->date_hired = $row['date_hired'];
            }
            return $result;
        } catch (Exception $e) {
            error_log("Error updating employee: " . $e->getMessage());
            return false;
        }
    }

    // Function to delete an employee record from the database
    public function delete($conn):bool{
        $sql = "DELETE FROM employees WHERE employee_id = ?";
        try {
            $stmt = mysqli_prepare($conn, $sql);
            if(!$stmt){
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, "i", $this->id);
            if(!mysqli_stmt_execute($stmt)){
                throw new Exception(mysqli_error($conn));
            }
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        } catch (Exception $e) {
            error_log("Error deleting employee: " . $e->getMessage());
            return false;
        }
    }

    // Function to retrieve employee records from the database by 'employee_id'
    // This function does not require an instance of the Employee class to be called, hence it is declared as static.
    public static function getById($conn, int $employee_id): ?Employee{
        $sql = "SELECT * FROM employees WHERE employee_id = ?";
        try {
            $stmt = mysqli_prepare($conn, $sql);
            if(!$stmt){
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $employee_id
            );
            if(!mysqli_stmt_execute($stmt)){
                throw new Exception(mysqli_error($conn));
            }
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            if($row){
                return new Employee (
                    $row['employee_id'], 
                    $row['first_name'], 
                    $row['last_name'], 
                    $row['email'], 
                    $row['department'], 
                    $row['position'], 
                    $row['date_hired']
                );
            }
            return null;
        } catch (Exception $e) {
            error_log("Error retrieving employee by ID: " . $e->getMessage());
            return null;
        }
    }

    public static function getAll($conn): ?array {
        try {
            $sql = "SELECT * FROM employees";
            $result = mysqli_query($conn, $sql);
            $employees = [];
            while($row = mysqli_fetch_assoc($result)){
                $employees[] = new Employee (
                    $row['employee_id'], 
                    $row['first_name'], 
                    $row['last_name'], 
                    $row['email'], 
                    $row['department'], 
                    $row['position'], 
                    $row['date_hired']
                );
            }
            mysqli_free_result($result);
            return $employees;
        } catch (Exception $e) {
            error_log("Error retrieving all employees: " . $e->getMessage());
            return null;
        }
    }
}