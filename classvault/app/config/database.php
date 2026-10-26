<?php
/**
 * ClassVault - Database Connection Manager
 * Menggunakan PDO Singleton dengan Prepared Statements sesuai spesifikasi.
 */

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/config.php';
            $db = $config['db'];

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $db['host'],
                $db['port'],
                $db['name'],
                $db['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$db['charset']} COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, $db['user'], $db['pass'], $options);
            } catch (PDOException $e) {
                // Jangan bocorkan kredensial atau detail database
                if (!empty($config['app_debug'])) {
                    die('Kesalahan Koneksi Database: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
                } else {
                    error_log('Database Connection Error: ' . $e->getMessage());
                    die('Layanan sistem basis data sedang mengalami kendala. Silakan coba beberapa saat lagi.');
                }
            }
        }

        return self::$instance;
    }
}
