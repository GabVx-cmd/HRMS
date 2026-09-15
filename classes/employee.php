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
        parent::__construct($db);
    }

    public function employeeLogin(string $email, int $employee_id): bool {
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
    }

    /**
     * Creates a new employee record in the database
     */
    public function createEmployee(array $data): bool {
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
    }

    /**
     * Retrieves all employees from the database
     */
    public function getAllEmployees(string $search, string $department, int $limit = 10, int $offset = 0): array {
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
    }

    /**
     * Retrieves an employee by their ID.
     */
    public function getEmployeeById(int $employee_id): bool|array {
        $query = "SELECT * FROM " . $this->empTable . " WHERE employee_id = :employee_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateEmployee(int $employee_id, array $data): bool {
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
    }

    public function deleteEmployee(int $employee_id): bool {
        $query = "DELETE FROM " . $this->empTable . " WHERE employee_id = :employee_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

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