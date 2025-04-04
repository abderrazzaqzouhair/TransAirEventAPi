<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'trans_air_event');
define('DB_USER', 'root');
define('DB_PASS', '');

class DB {
    private static $conn;
    
    public static function connect() {
        if (!self::$conn) {
            try {
                self::$conn = new PDO(
                    "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8",
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}

?>