<?php
/**
 * User class
 * Represents a user in the system
 * @var string $table The table to be used in the query
 * @var int|null $userId The ID of the user
 * @var int|null $employeeId The ID of the employee associated with the user
 * @var string|null $username The username of the user
 * @var string|null $password The password of the user
 * @var string|null $role The role of the user. This can be 'admin' and 'hr'. If null, the user is considered a regular employee
 * @throws Exception If the user is not authorized to perform certain actions
 */
class User {
    protected PDO $conn;
    protected string $table = "users";

    protected ?int $userId = null;
    protected ?int $employeeId = null;
    protected ?string $username = null;
    protected ?string $password = null;
    protected ?string $role = null;

    /**
     * Constructor injecting the database connection
     */
    public function __construct(PDO $db) {
        try {
            $this->conn = $db;
        } catch (Throwable $e) {
            error_log("User initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Authenticate a user by username and password
     * @param string $username The username of the user
     * @param string $password The password of the user
     * @return bool Returns true if the authentication is successful, false otherwise
     */

    public function login(string $username, string $password): bool {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch();
                if($password === $row['password']) {
                    $this->userId = $row['user_id'];
                    $this->employeeId = $row['employee_id'];
                    $this->username = $row['username'];
                    $this->password = $row['password'];
                    $this->role = $row['role'];

                    return true;
                }
            }
        } catch (Throwable $e) {
            error_log("User login error: " . $e->getMessage());
        } 
        return false;
    }

    /**
     * Checks if the user is an admin
     * @return bool Returns true if the user is an admin, false otherwise
     */
    private function isAdmin(): bool {
        try {
            return $this->role ? true : false; // If the role is not null, the user is considered an admin or hr
        } catch (Throwable $e) {
            error_log("User authorization error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new user account (HR/ADMIN feature)
     * @param int $employeeId The ID of the employee associated with the user
     * @param string $username The username of the user
     * @param string $password The password of the user
     * @param string $role The role of the user
     * @return bool Returns true if the user is created successfully, false otherwise
     * @throws Exception If the user is not authorized to perform certain actions
     */
    public function createUser(int $employeeId, string $username, string $password, string $role): bool {
        try {
            $admin = $this->isAdmin(); // Only admins can create users
            if(!$admin){
                throw new Exception("Unauthorized: Only admins can create users.");
            }
            $query = "INSERT INTO " . $this->table . " (employee_id, username, password, role) VALUES (:employeeId, :username, :password, :role)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam('employeeId', $employeeId, PDO::PARAM_INT);
            $stmt->bindParam('username', $username, PDO::PARAM_STR);
            $stmt->bindParam('password', $password, PDO::PARAM_STR);
            $stmt->bindParam('role', $role, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (Throwable $e) {
            error_log("User creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve a user by their userID
     * @param int $userId The ID of the user to retrieve
     * @return array|bool Returns the user data as an associative array if found, false
     */
    public function getUserById(int $userId): bool|array {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE user_id = :userId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            error_log("User lookup error: " . $e->getMessage());
        }
        return false;
    }

    /**
     * Encapsulated getter for the user properties
     * @return array Returns an associative array containing the user properties
     */
    public function getUserData(): array {
        try {
            return [
                'user_id' => $this->userId,
                'employee_id' => $this->employeeId,
                'username'=> $this->username,
                'password'=> $this->password,
                'role'=> $this->role
            ];
        } catch (Throwable $e) {
            error_log("User data error: " . $e->getMessage());
            return [];
        }
    }
}