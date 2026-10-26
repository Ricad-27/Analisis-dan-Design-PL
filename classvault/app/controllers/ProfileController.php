<?php
/**
 * ClassVault - ProfileController
 * Tahap 5: Profil Mahasiswa (/profile) dan Ubah Password (/change-password).
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/View.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Security.php';

class ProfileController {

    // -------------------------------------------------------------------------
    //  GET /profile — Tampilkan halaman profil
    // -------------------------------------------------------------------------
    public function showProfile(): void {
        AuthMiddleware::requireAuth();

        $userId    = (int)Session::get('user_id');
        $userModel = new User();
        $user      = $userModel->findById($userId);

        if (!$user) {
            Session::destroy();
            View::redirect('/login');
        }

        View::render('profile/index', [
            'page_title' => 'Profil Saya',
            'user'      => $user,
        ]);
    }

    // -------------------------------------------------------------------------
    //  POST /profile — Proses pembaruan profil
    // -------------------------------------------------------------------------
    public function updateProfile(): void {
        AuthMiddleware::requireAuth();
        Security::verifyCsrf($_POST['csrf_token'] ?? '');

        $userId    = (int)Session::get('user_id');
        $userModel = new User();

        $name         = trim($_POST['name'] ?? '');
        $programStudy = trim($_POST['program_study'] ?? '');
        $email        = trim($_POST['email'] ?? '');

        $errors = [];
        if (mb_strlen($name) < 2) {
            $errors['name'] = 'Nama lengkap minimal 2 karakter.';
        }
        if (mb_strlen($programStudy) < 2) {
            $errors['program_study'] = 'Program studi minimal 2 karakter.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }
        if ($email !== '' && $userModel->isEmailTaken($email, $userId)) {
            $errors['email'] = 'Email sudah digunakan oleh pengguna lain.';
        }

        if (!empty($errors)) {
            $user = $userModel->findById($userId);
            View::render('profile/index', [
                'page_title' => 'Profil Saya',
                'user'      => $user,
                'errors'    => $errors,
                'old'       => $_POST,
            ]);
            return;
        }

        $userModel->updateProfile($userId, [
            'name'          => $name,
            'program_study' => $programStudy,
            'email'         => $email,
        ]);

        // Sinkronisasi nama di sesi
        $sessionUser = Session::user() ?? [];
        $sessionUser['name'] = $name;
        $sessionUser['program_study'] = $programStudy;
        Session::set('user', $sessionUser);
        View::redirect('/profile', 'success', 'Profil berhasil diperbarui.');
    }

    // -------------------------------------------------------------------------
    //  GET /change-password — Tampilkan formulir ubah password
    // -------------------------------------------------------------------------
    public function showChangePassword(): void {
        AuthMiddleware::requireAuth();

        $userId    = (int)Session::get('user_id');
        $userModel = new User();
        $user      = $userModel->findById($userId);

        View::render('profile/change_password', [
            'page_title' => 'Ubah Password',
            'user'      => $user,
        ]);
    }

    // -------------------------------------------------------------------------
    //  POST /change-password — Proses ubah password
    // -------------------------------------------------------------------------
    public function changePassword(): void {
        AuthMiddleware::requireAuth();
        Security::verifyCsrf($_POST['csrf_token'] ?? '');

        $userId = (int)Session::get('user_id');
        $db     = Database::getConnection();

        // Ambil password_hash langsung — findById tidak mengembalikan password_hash
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!$row) {
            Session::destroy();
            View::redirect('/login');
        }

        $currentPw  = $_POST['current_password'] ?? '';
        $newPw      = $_POST['new_password'] ?? '';
        $confirmPw  = $_POST['confirm_password'] ?? '';
        $errors     = [];

        if (!password_verify($currentPw, $row['password_hash'])) {
            $errors['current_password'] = 'Password saat ini tidak sesuai.';
        }
        if (mb_strlen($newPw) < 8) {
            $errors['new_password'] = 'Password baru minimal 8 karakter.';
        }
        if ($newPw !== $confirmPw) {
            $errors['confirm_password'] = 'Konfirmasi password tidak cocok.';
        }

        if (!empty($errors)) {
            $userModel = new User();
            $user = $userModel->findById($userId);
            View::render('profile/change_password', [
                'page_title' => 'Ubah Password',
                'user'      => $user,
                'errors'    => $errors,
            ]);
            return;
        }

        $userModel = new User();
        $userModel->updatePassword($userId, $newPw);

        // Paksa login ulang setelah ganti password
        Session::destroy();
        View::redirect('/login', 'success', 'Password berhasil diubah. Silakan login kembali.');
    }
}
