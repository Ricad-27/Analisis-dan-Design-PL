<?php
/**
 * ClassVault - Authentication & Authorization Middleware
 * Menjaga hak akses rute berdasarkan status login dan peran pengguna.
 */

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/View.php';
require_once __DIR__ . '/../models/User.php';

class AuthMiddleware {
    /**
     * Memastikan pengguna telah masuk dan akun dalam status aktif
     */
    public static function requireAuth(): array {
        if (!Session::isLoggedIn()) {
            View::redirect('/login', 'warning', 'Silakan masuk ke akun Anda terlebih dahulu.');
        }

        // Verifikasi apakah akun masih aktif di basis data
        $userId = Session::userId();
        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user || $user['status'] !== 'active') {
            Session::destroy();
            View::redirect('/login', 'danger', 'Akun Anda telah dinonaktifkan oleh administrator.');
        }

        return $user;
    }

    /**
     * Memastikan hanya pengunjung tanpa login (guest) yang dapat membuka halaman tertentu (misal login & register)
     */
    public static function requireGuest(): void {
        if (Session::isLoggedIn()) {
            if (Session::isAdmin()) {
                View::redirect('/admin');
            } else {
                View::redirect('/dashboard');
            }
        }
    }

    /**
     * Memastikan pengguna adalah administrator
     */
    public static function requireAdmin(): array {
        $user = self::requireAuth();

        if ($user['role'] !== 'admin') {
            http_response_code(403);
            View::render('errors/403', [
                'page_title' => '403 - Akses Ditolak',
                'message' => 'Anda tidak memiliki hak akses administrator untuk membuka halaman ini.'
            ]);
            exit;
        }

        return $user;
    }

    /**
     * Memastikan pengguna adalah mahasiswa
     */
    public static function requireStudent(): array {
        $user = self::requireAuth();

        if ($user['role'] !== 'student') {
            http_response_code(403);
            View::render('errors/403', [
                'page_title' => '403 - Akses Ditolak',
                'message' => 'Halaman ini khusus untuk akun mahasiswa.'
            ]);
            exit;
        }

        return $user;
    }
}
