<?php
/**
 * Database Connection Class
 * Handles database connections and queries with security features
 */

require_once __DIR__ . '/../config/config.php';

class Database {
    private $conn;
    private static $instance = null;
    
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Singleton pattern to ensure single database connection
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->conn->connect_error) {
            error_log("Database Connection Failed: " . $this->conn->connect_error);
            die("Database connection failed. Please try again later.");
        }
        
        $this->conn->set_charset("utf8mb4");
    }
    
    /**
     * Get the mysqli connection object
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Execute a prepared statement query
     * @param string $sql SQL query with placeholders
     * @param string $types Parameter types (e.g., "ssi" for string, string, integer)
     * @param array $params Array of parameters
     * @return mysqli_stmt|false
     */
    public function prepare($sql, $types = "", $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Query preparation failed: " . $this->conn->error);
            return false;
        }
        
        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        return $stmt;
    }
    
    /**
     * Execute a query and return results
     * @param string $sql SQL query
     * @return mysqli_result|bool
     */
    public function query($sql) {
        $result = $this->conn->query($sql);
        
        if (!$result) {
            error_log("Query execution failed: " . $this->conn->error);
        }
        
        return $result;
    }
    
    /**
     * Escape string for safe SQL usage
     * @param string $value Value to escape
     * @return string
     */
    public function escape($value) {
        return $this->conn->real_escape_string($value);
    }
    
    /**
     * Get last inserted ID
     * @return int
     */
    public function getLastInsertId() {
        return $this->conn->insert_id;
    }
    
    /**
     * Get number of affected rows
     * @return int
     */
    public function getAffectedRows() {
        return $this->conn->affected_rows;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        $this->conn->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->conn->rollback();
    }
    
    /**
     * Close database connection
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    /**
     * Prevent cloning of instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
