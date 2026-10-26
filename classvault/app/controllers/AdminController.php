<?php
/**
 * ClassVault - AdminController
 * Menangani dashboard dan manajemen akun pengguna oleh administrator.
 */

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/View.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Security.php';
require_once __DIR__ . '/../helpers/Session.php';

class AdminController {
    public function dashboard(): void {
        $admin = AuthMiddleware::requireAdmin();
        $db = Database::getConnection();

        // Statistik Pengguna & Dokumen Global
        $stmtUsers = $db->query("
            SELECT 
                COUNT(*) AS total_users,
                SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) AS total_students,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS total_active,
                SUM(CASE WHEN status = 'disabled' THEN 1 ELSE 0 END) AS total_disabled
            FROM users
        ");
        $userStats = $stmtUsers->fetch();

        $stmtDocs = $db->query("
            SELECT 
                COUNT(*) AS total_docs,
                COALESCE(SUM(file_size), 0) AS total_bytes,
                SUM(CASE WHEN visibility = 'public' THEN 1 ELSE 0 END) AS total_public,
                SUM(CASE WHEN visibility = 'authenticated' THEN 1 ELSE 0 END) AS total_authenticated,
                SUM(CASE WHEN visibility = 'private' THEN 1 ELSE 0 END) AS total_private
            FROM documents
            WHERE deleted_at IS NULL
        ");
        $docStats = $stmtDocs->fetch();

        View::render('admin/dashboard', [
            'page_title' => 'Dashboard Administrasi',
            'admin'      => $admin,
            'userStats'  => $userStats,
            'docStats'   => $docStats
        ]);
    }

    // -------------------------------------------------------------------------
    //  GET /admin/users — daftar & pencarian pengguna
    // -------------------------------------------------------------------------
    public function users(): void {
        AuthMiddleware::requireAdmin();

        $search = Security::sanitize($_GET['q'] ?? '');
        $role   = Security::sanitize($_GET['role'] ?? '');
        $status = Security::sanitize($_GET['status'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));

        $result = (new User())->searchUsers($search, $role, $status, $page);

        View::render('admin/users', [
            'page_title' => 'Kelola Pengguna',
            'users'      => $result['items'],
            'pagination' => $result,
            'search'     => $search,
            'role'       => $role,
            'status'     => $status,
        ]);
    }

    // -------------------------------------------------------------------------
    //  GET /admin/users/{id} — detail pengguna
    // -------------------------------------------------------------------------
    public function userDetail(string $id): void {
        $admin = AuthMiddleware::requireAdmin();
        $userModel = new User();
        $target = $userModel->findById((int)$id);

        if (!$target) {
            http_response_code(404);
            View::render('errors/404', ['page_title' => 'Pengguna Tidak Ditemukan']);
            return;
        }

        // Password sementara hasil reset: ditampilkan satu kali lalu dihapus dari sesi
        $tempPassword = null;
        $flashTemp = Session::get('admin_temp_password');
        if ($flashTemp && (int)$flashTemp['user_id'] === (int)$target['id']) {
            $tempPassword = $flashTemp['password'];
        }
        Session::remove('admin_temp_password');

        View::render('admin/user_detail', [
            'tempPassword' => $tempPassword,
            'page_title' => 'Detail Pengguna',
            'admin'      => $admin,
            'target'     => $target,
            'stats'      => $userModel->getDocumentStats((int)$target['id']),
        ]);
    }

    // -------------------------------------------------------------------------
    //  POST /admin/users/{id}/status — aktifkan / nonaktifkan akun
    // -------------------------------------------------------------------------
    public function toggleStatus(string $id): void {
        $admin = AuthMiddleware::requireAdmin();
        Security::verifyCsrf($_POST['csrf_token'] ?? '');

        $userModel = new User();
        $targetId  = (int)$id;
        $target    = $userModel->findById($targetId);
        $back      = '/admin/users/' . $targetId;

        if (!$target) {
            View::redirect('/admin/users', 'danger', 'Pengguna tidak ditemukan.');
        }
        if ($targetId === (int)$admin['id']) {
            View::redirect($back, 'danger', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }
        if ($target['role'] === 'admin') {
            View::redirect($back, 'danger', 'Akun administrator tidak dapat dinonaktifkan melalui panel ini.');
        }

        $newStatus = $target['status'] === 'active' ? 'disabled' : 'active';
        $userModel->setStatus($targetId, $newStatus);

        View::redirect($back, 'success', $newStatus === 'disabled'
            ? 'Akun berhasil dinonaktifkan. Pengguna tidak dapat masuk atau mengakses sistem.'
            : 'Akun berhasil diaktifkan kembali.');
    }

    // -------------------------------------------------------------------------
    //  POST /admin/users/{id}/reset-password — password sementara acak (tampil sekali)
    // -------------------------------------------------------------------------
    public function resetPassword(string $id): void {
        $admin = AuthMiddleware::requireAdmin();
        Security::verifyCsrf($_POST['csrf_token'] ?? '');

        $userModel = new User();
        $targetId  = (int)$id;
        $target    = $userModel->findById($targetId);
        $back      = '/admin/users/' . $targetId;

        if (!$target) {
            View::redirect('/admin/users', 'danger', 'Pengguna tidak ditemukan.');
        }
        if ($target['role'] === 'admin') {
            View::redirect($back, 'danger', 'Password administrator tidak dapat direset melalui panel ini.');
        }

        // Password sementara acak; password lama pengguna tidak pernah ditampilkan
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $temp = '';
        for ($i = 0; $i < 12; $i++) {
            $temp .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $userModel->updatePassword($targetId, $temp);

        Session::set('admin_temp_password', ['user_id' => $targetId, 'password' => $temp]);
        View::redirect($back, 'success', 'Password berhasil direset. Salin password sementara di bawah dan berikan kepada pengguna.');
    }
}
