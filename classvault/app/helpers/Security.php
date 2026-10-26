<?php
/**
 * ClassVault - Security Helper
 * Penanganan XSS, CSRF, normalisasi string, dan keamanan file.
 */

class Security {
    /**
     * Escape output untuk mencegah XSS
     */
    public static function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Bersihkan input teks pencarian/filter: trim, buang karakter kontrol, batasi panjang.
     * (Output tetap harus di-escape dengan e() saat ditampilkan.)
     */
    public static function sanitize(?string $value, int $maxLength = 200): string {
        $value = trim((string)$value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }

    /**
     * Normalisasi string untuk institusi & NIM (trim, lowercase, single space)
     */
    public static function normalize(string $value): string {
        $value = trim($value);
        $value = mb_strtolower($value, 'UTF-8');
        // Ganti spasi ganda atau tab menjadi satu spasi
        return preg_replace('/\s+/', ' ', $value);
    }

    /**
     * Generate CSRF Token
     */
    public static function csrfToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            Session::init();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Input hidden CSRF token untuk form HTML
     */
    public static function csrfField(): string {
        $token = self::csrfToken();
        return '<input type="hidden" name="csrf_token" value="' . self::e($token) . '">';
    }

    /**
     * Validasi CSRF Token
     */
    public static function validateCsrf(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            Session::init();
        }

        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Verifikasi CSRF untuk handler POST: jika tidak valid, hentikan request dengan halaman 403.
     */
    public static function verifyCsrf(?string $token): void {
        if (self::validateCsrf($token)) {
            return;
        }
        http_response_code(403);
        View::render('errors/403', [
            'page_title' => '403 - Sesi Tidak Valid',
            'message'    => 'Sesi form telah berakhir atau token keamanan tidak valid. Silakan muat ulang halaman dan coba lagi.',
        ]);
        exit;
    }

    /**
     * Buat nama file penyimpanan internal acak dan aman
     */
    public static function generateStorageName(string $extension): string {
        $cleanExt = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $extension));
        return bin2hex(random_bytes(16)) . '_' . time() . ($cleanExt !== '' ? '.' . $cleanExt : '');
    }

    /**
     * Bersihkan nama file asli agar aman saat diunduh
     */
    public static function sanitizeOriginalFilename(string $filename): string {
        $filename = basename($filename);
        // Hapus karakter berbahaya
        $filename = preg_replace('/[^\p{L}\p{N}\s\-_.]/u', '', $filename);
        $filename = trim($filename);
        return $filename !== '' ? $filename : 'dokumen';
    }
}
