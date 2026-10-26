<?php
/**
 * ClassVault - Session Manager
 * Mengatur session lifecycle, flash message, cookie parameters, dan status autentikasi.
 */

class Session {
    private static bool $started = false;

    /**
     * Inisialisasi session dengan pengaturan keamanan cookie
     */
    public static function init(): void {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $config = require __DIR__ . '/../config/config.php';
        $lifetime = $config['session']['lifetime'] ?? 7200;
        $isSecure = !empty($config['session']['secure']) || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        if (!headers_sent()) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');

            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();
        } elseif (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        self::$started = (session_status() === PHP_SESSION_ACTIVE);
    }

    public static function set(string $key, mixed $value): void {
        self::init();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        self::init();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        self::init();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        self::init();
        unset($_SESSION[$key]);
    }

    /**
     * Regenerasi Session ID untuk mencegah session fixation setelah login
     */
    public static function regenerate(): void {
        self::init();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }

    /**
     * Simpan pesan kilat (flash message)
     */
    public static function setFlash(string $type, string $message): void {
        self::init();
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Ambil dan hapus seluruh pesan flash
     */
    public static function getFlashes(): array {
        self::init();
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }

    /**
     * Alias setFlash() agar konsisten dengan pemanggilan di controller Tahap 5
     */
    public static function flash(string $type, string $message): void {
        self::setFlash($type, $message);
    }

    /**
     * Ambil dan hapus satu pesan flash berdasarkan tipe (null jika tidak ada)
     */
    public static function getFlash(string $type): ?string {
        self::init();
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    /**
     * Alias isLoggedIn()
     */
    public static function check(): bool {
        return self::isLoggedIn();
    }

    /**
     * Status login pengguna
     */
    public static function isLoggedIn(): bool {
        self::init();
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array {
        self::init();
        return $_SESSION['user'] ?? null;
    }

    public static function userId(): ?int {
        self::init();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function role(): ?string {
        self::init();
        return $_SESSION['user_role'] ?? null;
    }

    public static function isAdmin(): bool {
        return self::isLoggedIn() && self::role() === 'admin';
    }

    public static function isStudent(): bool {
        return self::isLoggedIn() && self::role() === 'student';
    }

    /**
     * Hancurkan session saat logout
     */
    public static function destroy(): void {
        self::init();
        $_SESSION = [];

        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        self::$started = false;
    }
}
