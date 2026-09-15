<?php
require_once 'user.php';
/**
 * Employee class model
 * Manages the CRUD operations for the employees table in the database
 */
class Employee extends User {
    private $empTable = 'employees';
    protected ?string $fname = null;
    protected ?string $lname = null;
    protected ?string $email = null;
    protected ?string $phone = null;
    protected ?string $address = null;
    protected ?string $department = null;
    protected ?string $position = null;
    protected ?string $dateHired = null;

    public function __construct(PDO $db) {
        try {
            parent::__construct($db);
        } catch (Throwable $e) {
            error_log("Employee initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Authenticates an employee by email and employee ID
     * @param string $email The email of the employee
     * @param int $employee_id The ID of the employee
     * @return bool Returns true if the authentication is successful, false otherwise
     */
    public function employeeLogin(string $email, int $employee_id): bool {
        try {
            $query = "SELECT * FROM " . $this->empTable . " WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch();
                if ($row['employee_id'] === $employee_id) {
                    $this->employeeId = $row['employee_id'];
                    $this->fname = $row['first_name'];
                    $this->lname = $row['last_name'];
                    $this->email = $row['email'];
                    $this->phone = $row['phone'];
                    $this->address = $row['address'];
                    $this->department = $row['department'];
                    $this->position = $row['position'];
                    $this->dateHired = $row['date_hired'];
                    return true;
                }
            }
            return false;
        } catch (Throwable $e) {
            error_log("Error during employee login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new employee record in the database.
     * @param array $data An associative array containing employee data
     * @return bool Returns true if the employee was created successfully, false otherwise.
     */
    public function createEmployee(array $data): bool {
        try {
            $query = "INSERT INTO " . $this->empTable . " (first_name, last_name, email, phone, address, date_hired, department, position)
            VALUES (:first_name, :last_name, :email, :phone, :address, :date_hired, :department, :position)";
    
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $data['first_name'], PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $data['last_name'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
            $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
            $stmt->bindParam(':date_hired', $data['date_hired'], PDO::PARAM_STR);
            $stmt->bindParam(':department', $data['department'], PDO::PARAM_STR);
            $stmt->bindParam(':position', $data['position'], PDO::PARAM_STR);
    
            return $stmt->execute();
        } catch (Throwable $e) {
            error_log("Error creating employee: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves all employees from the database
     * @param string $search Optional search term to filter employees by first name, last name, or email.
     * @param string $department Optional department filter to retrieve employees from a specific department.
     * @param int $limit The maximum number of records to retrieve (default is 10).
     * @param int $offset The number of records to skip (default is 0).
     * @return array Returns an array of employees matching the criteria.
     */
    public function getAllEmployees(string $search, string $department, int $limit = 10, int $offset = 0): array {
        try {
            $query = "SELECT * FROM " . $this->empTable . " WHERE 1=1";
            if (!empty($search)) {
                $query .= " AND (first_name LIKE :search1 OR last_name LIKE :search2 OR email LIKE :search3)";
            }
            if (!empty($department)) {
                $query .= " AND department = :department";
            }
            $query .= " ORDER BY employee_id DESC LIMIT :limit OFFSET :offset";
    
            $stmt = $this->conn->prepare($query);
            if (!empty($search)) {
                $searchParam = "%$search%";
                $stmt->bindParam(':search1', $searchParam, PDO::PARAM_STR);
                $stmt->bindParam(':search2', $searchParam, PDO::PARAM_STR);
                $stmt->bindParam(':search3', $searchParam, PDO::PARAM_STR);
            }
            if (!empty($department)) {
                $stmt->bindParam(':department', $department, PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
    
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Error fetching employees: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieves an employee by their ID.
     * @param int $employee_id The ID of the employee to retrieve.
     * @return bool|array Returns false if the employee is not found, or an array containing the employee's data if found.
     */
    public function getEmployeeById(int $employee_id): bool|array {
        try {
            $query = "SELECT * FROM " . $this->empTable . " WHERE employee_id = :employee_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Error fetching employee by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an employee's information.
     * @param int $employee_id The ID of the employee to update.
     * @param array $data An array containing the updated employee data.
     * @return bool Returns true if the employee is successfully updated, false otherwise.
     */
    public function updateEmployee(int $employee_id, array $data): bool {
        try {
            $query = "UPDATE " . $this->empTable . " SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, address = :address, date_hired = :date_hired, department = :department, position = :position 
            WHERE employee_id = :employee_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $data['first_name'], PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $data['last_name'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
            $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
            $stmt->bindParam(':date_hired', $data['date_hired'], PDO::PARAM_STR);
            $stmt->bindParam(':department', $data['department'], PDO::PARAM_STR);
            $stmt->bindParam(':position', $data['position'], PDO::PARAM_STR);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Throwable $e) {
            error_log("Error updating employee: " . $e->getMessage());
            return false;
        }
    }

    public function deleteEmployee(int $employee_id): bool {
        try {
            $query = "DELETE FROM " . $this->empTable . " WHERE employee_id = :employee_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Throwable $e) {
            error_log("Error deleting employee: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Summary of getEmployeeData
     * @return array{address: string|null, "date_hired": string|null, department: string|null, email: string|null, "employee_id": int|null, "first_name": string|null, "last_name": string|null, phone: string|null, position: string|null}
     */
    public function getEmployeeData(): array {
        return [
            'employee_id' => $this->employeeId,
            'first_name' => $this->fname,
            'last_name' => $this->lname,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'department' => $this->department,
            'position' => $this->position,
            'date_hired' => $this->dateHired
        ];
    }
}