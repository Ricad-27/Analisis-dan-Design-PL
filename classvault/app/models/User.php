<?php
/**
 * ClassVault - User Model
 * Menangani operasi database terkait akun pengguna (mahasiswa & admin).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Security.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Cari pengguna berdasarkan ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT id, name, institution, nim, program_study, email, role, status, created_at, updated_at 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Cari pengguna untuk autentikasi (mengambil password_hash juga)
     * Mendukung pencarian berdasarkan normalisasi (Institusi + NIM) atau Email
     */
    public function findForAuth(string $institution, string $nim): ?array {
        $institutionNorm = Security::normalize($institution);
        $nimNorm = Security::normalize($nim);

        $stmt = $this->db->prepare("
            SELECT id, name, institution, nim, program_study, email, password_hash, role, status 
            FROM users 
            WHERE institution_normalized = ? AND nim_normalized = ?
        ");
        $stmt->execute([$institutionNorm, $nimNorm]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Cari pengguna berdasarkan email (jika pengguna mencoba login atau cek duplikasi)
     */
    public function findByEmail(string $email): ?array {
        $email = trim(mb_strtolower($email, 'UTF-8'));
        $stmt = $this->db->prepare("
            SELECT id, name, institution, nim, program_study, email, password_hash, role, status 
            FROM users 
            WHERE LOWER(email) = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Periksa apakah kombinasi institusi dan NIM sudah terdaftar
     */
    public function isInstitutionNimTaken(string $institution, string $nim, ?int $excludeUserId = null): bool {
        $institutionNorm = Security::normalize($institution);
        $nimNorm = Security::normalize($nim);

        $sql = "SELECT id FROM users WHERE institution_normalized = ? AND nim_normalized = ?";
        $params = [$institutionNorm, $nimNorm];

        if ($excludeUserId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    /**
     * Periksa apakah email sudah terdaftar
     */
    public function isEmailTaken(string $email, ?int $excludeUserId = null): bool {
        $email = trim(mb_strtolower($email, 'UTF-8'));
        if ($email === '') {
            return false;
        }

        $sql = "SELECT id FROM users WHERE LOWER(email) = ?";
        $params = [$email];

        if ($excludeUserId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    /**
     * Registrasi mahasiswa baru
     */
    public function registerStudent(array $data): int {
        $institutionNorm = Security::normalize($data['institution']);
        $nimNorm = Security::normalize($data['nim']);
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $email = !empty($data['email']) ? trim($data['email']) : null;

        $stmt = $this->db->prepare("
            INSERT INTO users (
                name, institution, institution_normalized, nim, nim_normalized,
                program_study, email, password_hash, role, status
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, 'student', 'active'
            )
        ");

        $stmt->execute([
            trim($data['name']),
            trim($data['institution']),
            $institutionNorm,
            trim($data['nim']),
            $nimNorm,
            trim($data['program_study']),
            $email,
            $passwordHash
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Periksa jumlah percobaan login gagal dalam rentang waktu (default 15 menit, batas 5 kali)
     */
    public function isRateLimited(string $ip, string $identifier, int $maxAttempts = 5, int $decayMinutes = 15): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total 
            FROM login_attempts 
            WHERE (ip_address = ? OR identifier = ?) 
              AND attempted_at >= (NOW() - INTERVAL ? MINUTE)
        ");
        $stmt->execute([$ip, $identifier, $decayMinutes]);
        $result = $stmt->fetch();
        return ($result['total'] ?? 0) >= $maxAttempts;
    }

    /**
     * Catat percobaan login gagal
     */
    public function recordFailedLogin(string $ip, string $identifier): void {
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (ip_address, identifier, attempted_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$ip, $identifier]);
    }

    /**
     * Bersihkan riwayat login gagal setelah berhasil masuk
     */
    public function clearLoginAttempts(string $ip, string $identifier): void {
        $stmt = $this->db->prepare("
            DELETE FROM login_attempts 
            WHERE ip_address = ? OR identifier = ?
        ");
        $stmt->execute([$ip, $identifier]);
    }

    /**
     * Perbarui profil mahasiswa
     */
    public function updateProfile(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET name = ?, program_study = ?, email = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            trim($data['name']),
            trim($data['program_study']),
            !empty($data['email']) ? trim($data['email']) : null,
            $id
        ]);
    }

    /**
     * Perbarui password pengguna
     */
    public function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    /**
     * Ubah status akun (active / disabled) oleh admin
     */
    public function setStatus(int $id, string $status): bool {
        if (!in_array($status, ['active', 'disabled'], true)) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /**
     * Daftar pengguna untuk admin: pencarian, filter peran/status, pagination.
     * Hanya mengembalikan data akun dasar (tanpa password_hash).
     */
    public function searchUsers(string $search = '', string $role = '', string $status = '', int $page = 1, int $perPage = 10): array {
        $where = ['1=1'];
        $params = [];

        if ($search !== '') {
            $where[] = "(u.name LIKE ? OR u.institution LIKE ? OR u.nim LIKE ? OR u.email LIKE ?)";
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if (in_array($role, ['student', 'admin'], true)) {
            $where[] = "u.role = ?";
            $params[] = $role;
        }
        if (in_array($status, ['active', 'disabled'], true)) {
            $where[] = "u.status = ?";
            $params[] = $status;
        }
        $whereSql = implode(' AND ', $where);

        $count = $this->db->prepare("SELECT COUNT(*) FROM users u WHERE {$whereSql}");
        $count->execute($params);
        $total = (int)$count->fetchColumn();

        $page = max($page, 1);
        $totalPages = max((int)ceil($total / $perPage), 1);
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.institution, u.nim, u.program_study, u.email, u.role, u.status, u.created_at,
                   (SELECT COUNT(*) FROM documents d WHERE d.owner_id = u.id AND d.deleted_at IS NULL) AS doc_count
            FROM users u
            WHERE {$whereSql}
            ORDER BY u.created_at DESC, u.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);

        return [
            'items'        => $stmt->fetchAll(),
            'total_items'  => $total,
            'current_page' => $page,
            'total_pages'  => $totalPages,
            'per_page'     => $perPage,
        ];
    }

    /**
     * Statistik dokumen milik satu pengguna (hanya agregat: admin tidak membaca isi/judul dokumen private)
     */
    public function getDocumentStats(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total_docs,
                   COALESCE(SUM(file_size), 0) AS total_bytes,
                   COALESCE(SUM(visibility = 'public'), 0) AS total_public,
                   COALESCE(SUM(visibility = 'authenticated'), 0) AS total_authenticated,
                   COALESCE(SUM(visibility = 'private'), 0) AS total_private,
                   MAX(created_at) AS last_upload
            FROM documents
            WHERE owner_id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Jumlah admin aktif (untuk mencegah admin terakhir dinonaktifkan)
     */
    public function countActiveAdmins(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'")->fetchColumn();
    }
}
