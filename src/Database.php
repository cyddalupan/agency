<?php

class DatabaseConnection
{
    private static $pdo = null;

    public static function getConnection()
    {
        if (self::$pdo === null) {
            // Ensure config.php is loaded if not already defined
            if (!defined('DB_HOST')) {
                require_once(dirname(__DIR__) . '/config.php');
            }

            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                $stmt = self::$pdo->query("SELECT @@SESSION.sql_mode");
                $sql_mode = $stmt->fetchColumn();
                error_log("MySQL SESSION sql_mode after connection: " . $sql_mode);
            } catch (PDOException $e) {
                // In a real application, you might log this error and show a user-friendly message.
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
