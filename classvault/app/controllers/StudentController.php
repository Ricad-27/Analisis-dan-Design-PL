<?php
/**
 * ClassVault - StudentController
 * Menangani dashboard dan aktivitas utama mahasiswa.
 */

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/View.php';
require_once __DIR__ . '/../config/database.php';

class StudentController {
    public function dashboard(): void {
        $user = AuthMiddleware::requireStudent();
        $db = Database::getConnection();

        // Hitung statistik dokumen milik mahasiswa
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) AS total_files,
                COALESCE(SUM(file_size), 0) AS total_bytes,
                SUM(CASE WHEN visibility = 'private' THEN 1 ELSE 0 END) AS private_count,
                SUM(CASE WHEN visibility IN ('authenticated', 'public') THEN 1 ELSE 0 END) AS shared_count
            FROM documents 
            WHERE owner_id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$user['id']]);
        $stats = $stmt->fetch();

        View::render('student/dashboard', [
            'page_title' => 'Dashboard Mahasiswa',
            'user'       => $user,
            'stats'      => $stats
        ]);
    }
}
