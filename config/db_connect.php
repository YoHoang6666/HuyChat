<?php
// db_connect.php
class Database {
    private static $instance = null;
    private $pdo;

    // InfinityFree database credentials
    private function __construct() {
        try {
            $this->pdo = new PDO(
                'mysql:host=sql106.infinityfree.com;dbname=if0_37967376_userinfo;charset=utf8mb4',
                'if0_37967376', 
                'Hoanggiahuy',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection error. Please try again later.");
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}

// Initialize connection (optional, can call getInstance() directly when needed)
$pdo = Database::getInstance();
?>