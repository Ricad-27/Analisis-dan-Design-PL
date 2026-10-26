<?php
/**
 * ClassVault - Application Configuration
 * Membaca konfigurasi dari file .env secara aman tanpa dependensi eksternal.
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
    define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
    define('STORAGE_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'storage');
    define('PUBLIC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'public');
}

if (!function_exists('load_env')) {
    /**
     * Helper untuk membaca file .env
     */
    function load_env(string $path): array {
        $variables = [];
        if (!file_exists($path)) {
            return $variables;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($key, $val) = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val);

                // Bersihkan kutip jika ada
                if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                    (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                    $val = substr($val, 1, -1);
                }

                // Konversi nilai khusus
                $valLower = strtolower($val);
                if ($valLower === 'true') {
                    $val = true;
                } elseif ($valLower === 'false') {
                    $val = false;
                } elseif ($valLower === 'null') {
                    $val = null;
                }

                $variables[$key] = $val;
                $_ENV[$key] = $val;
            }
        }

        return $variables;
    }
}

// Muat .env jika ada
$env = load_env(ROOT_PATH . DIRECTORY_SEPARATOR . '.env');

if (!function_exists('detect_base_url')) {
    /**
     * Deteksi base URL otomatis agar fleksibel di lingkungan XAMPP
     */
    function detect_base_url(): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Deteksi sub-direktori dari script path
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dirName = str_replace('\\', '/', dirname($scriptName));
        $dirName = rtrim($dirName, '/');
        
        return $scheme . '://' . $host . ($dirName !== '' ? $dirName : '');
    }
}

$detectedUrl = detect_base_url();

$config = [
    'app_name' => $env['APP_NAME'] ?? 'ClassVault',
    'app_env' => $env['APP_ENV'] ?? 'local',
    'app_debug' => $env['APP_DEBUG'] ?? true,
    'app_url' => !empty($env['APP_URL']) ? rtrim($env['APP_URL'], '/') : $detectedUrl,
    
    // Konfigurasi Database
    'db' => [
        'host' => $env['DB_HOST'] ?? '127.0.0.1',
        'port' => (int)($env['DB_PORT'] ?? 3306),
        'name' => $env['DB_NAME'] ?? 'classvault_db',
        'user' => $env['DB_USER'] ?? 'root',
        'pass' => $env['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
    ],
    
    // Pengaturan Upload Dokumen
    'upload' => [
        'max_size_mb' => (int)($env['MAX_FILE_SIZE_MB'] ?? 10),
        'allowed_extensions' => explode(',', $env['ALLOWED_EXTENSIONS'] ?? 'pdf,docx,xlsx,pptx,txt,jpg,jpeg,png'),
        'allowed_mimes' => [
            'pdf'  => ['application/pdf'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'txt'  => ['text/plain'],
            'jpg'  => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png'  => ['image/png'],
        ],
        'storage_path' => STORAGE_PATH . DIRECTORY_SEPARATOR . 'uploads',
    ],
    
    // Session & Keamanan
    'session' => [
        'lifetime' => (int)($env['SESSION_LIFETIME'] ?? 7200),
        'secure' => (bool)($env['SECURE_COOKIES'] ?? false),
    ],
];

// Konstanta global base URL (dipakai controller/view Tahap 5)
if (!defined('BASE_URL')) {
    define('BASE_URL', $config['app_url']);
}

// Atur error reporting berdasarkan mode debug
if ($config['app_debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

return $config;
