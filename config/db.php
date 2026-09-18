<?php
/**
 * Database configuration file
 * manages PDO connection to the db
 * @var PDO $conn The PDO connection object
 * @var string $host The database host
 * @var string $username The database username
 * @var string $password The database password
 * @var string $dbname The database name
 * @var int $port The database port
 * @throws PDOException If the connection fails, an exception is thrown with the error message
 * Change the database credentials as per your setup
 */
class db {
    private string $host;
    private string $username;
    private string $password;
    private string $dbname;
    private int $port;
    private string $ssl_ca;
    private ?PDO $conn = null;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->username = $_ENV['DB_USER'] ??'root';
        $this->password = $_ENV['DB_PASS'] ??'password';
        $this->dbname = $_ENV['DB_NAME'] ??'human_resource_management_system';
        $this->port = isset($_ENV['DB_PORT']) ? (int)$_ENV['DB_PORT'] : 3306;

        $this->ssl_ca = __DIR__ .'/../certs/DigiCertGlobalRootG2.crt.pem';
    }

    /**
     * Establishes a PDO connection to the database.
     * @return PDO|null Returns the PDO connection object if successful, null otherwise.
     */
    public function getConnection(): ?PDO {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";port=" . $this->port . ";charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Set error mode to exception
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Set default fetch mode to associative array
                PDO::ATTR_EMULATE_PREPARES => false, // Disable emulation of prepared statements for better security
                PDO::MYSQL_ATTR_SSL_CA => $this->ssl_ca,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

        } catch (PDOException $e ) {
            // Logs the error message to server logs
            error_log("Database connection error: " . $e->getMessage());

            // Display a generic error message to the user without exposing sensitive details
            die("Database connection failed. Please try again later." );
        }
        return $this->conn;
    }
}