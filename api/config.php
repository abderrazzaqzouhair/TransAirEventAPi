<?php
class DB {
    private static $conn;
    
    public static function connect() {
        if (!self::$conn) {
            // Load environment variables
            $env = parse_ini_file(__DIR__ . '/../.env');
            
            if (!$env) {
                die("Error loading .env file");
            }

            try {
                self::$conn = new PDO(
                    "mysql:host=" . $env['DB_HOST'] . ";dbname=" . $env['DB_NAME'] . ";charset=utf8",
                    $env['DB_USER'],
                    $env['DB_PASS'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
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