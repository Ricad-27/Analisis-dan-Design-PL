<?php
/**
 * ClassVault - Document Model
 * Menangani operasi database untuk dokumen akademik (CRUD, filter, kepemilikan, soft delete).
 */

require_once __DIR__ . '/../config/database.php';

class Document {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Ambil seluruh kategori dokumen untuk dropdown form
     */
    public function getCategories(): array {
        $stmt = $this->db->query("SELECT id, name FROM categories ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Cari dokumen berdasarkan ID (hanya yang belum di-soft-delete secara default)
     */
    public function findById(int $id, bool $includeDeleted = false): ?array {
        $sql = "
            SELECT d.*, c.name AS category_name, u.name AS owner_name, u.institution AS owner_institution
            FROM documents d
            JOIN categories c ON d.category_id = c.id
            JOIN users u ON d.owner_id = u.id
            WHERE d.id = ?
        ";

        if (!$includeDeleted) {
            $sql .= " AND d.deleted_at IS NULL";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $doc = $stmt->fetch();
        return $doc ?: null;
    }

    /**
     * Simpan dokumen baru
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO documents (
                owner_id, category_id, title, description,
                original_filename, storage_filename, mime_type, file_size,
                visibility, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, NOW(), NOW()
            )
        ");

        $stmt->execute([
            $data['owner_id'],
            $data['category_id'],
            trim($data['title']),
            !empty($data['description']) ? trim($data['description']) : null,
            $data['original_filename'],
            $data['storage_filename'],
            $data['mime_type'],
            $data['file_size'],
            $data['visibility'] ?? 'private'
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Perbarui metadata dokumen (hanya jika pemilik sah)
     */
    public function updateMetadata(int $id, int $ownerId, array $data): bool {
        // Verifikasi kepemilikan dokumen
        $check = $this->db->prepare("SELECT id FROM documents WHERE id = ? AND owner_id = ? AND deleted_at IS NULL");
        $check->execute([$id, $ownerId]);
        if (!$check->fetch()) {
            return false;
        }

        $sql = "
            UPDATE documents 
            SET title = ?, description = ?, category_id = ?, visibility = ?, updated_at = NOW()
        ";
        $params = [
            trim($data['title']),
            !empty($data['description']) ? trim($data['description']) : null,
            (int)$data['category_id'],
            $data['visibility'],
        ];

        // Jika ada penggantian file baru
        if (!empty($data['storage_filename'])) {
            $sql .= ", original_filename = ?, storage_filename = ?, mime_type = ?, file_size = ?";
            $params[] = $data['original_filename'];
            $params[] = $data['storage_filename'];
            $params[] = $data['mime_type'];
            $params[] = $data['file_size'];
        }

        $sql .= " WHERE id = ? AND owner_id = ? AND deleted_at IS NULL";
        $params[] = $id;
        $params[] = $ownerId;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Soft delete dokumen oleh pemilik
     */
    public function softDelete(int $id, int $ownerId): bool {
        // Verifikasi kepemilikan dokumen
        $check = $this->db->prepare("SELECT id FROM documents WHERE id = ? AND owner_id = ? AND deleted_at IS NULL");
        $check->execute([$id, $ownerId]);
        if (!$check->fetch()) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE documents 
            SET deleted_at = NOW() 
            WHERE id = ? AND owner_id = ? AND deleted_at IS NULL
        ");
        return $stmt->execute([$id, $ownerId]);
    }

    /**
     * Ambil daftar dokumen milik pengguna dengan pencarian, filter, pengurutan, dan pagination
     */
    public function getMyDocuments(
        int $ownerId,
        string $search = '',
        int $categoryId = 0,
        string $visibility = '',
        string $sort = 'date_desc',
        int $page = 1,
        int $perPage = 10
    ): array {
        $where = ["d.owner_id = ?", "d.deleted_at IS NULL"];
        $params = [$ownerId];

        // Filter pencarian
        if ($search !== '') {
            $where[] = "(d.title LIKE ? OR d.description LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Filter kategori
        if ($categoryId > 0) {
            $where[] = "d.category_id = ?";
            $params[] = $categoryId;
        }

        // Filter visibilitas
        if (in_array($visibility, ['private', 'authenticated', 'public'], true)) {
            $where[] = "d.visibility = ?";
            $params[] = $visibility;
        }

        $whereClause = implode(' AND ', $where);

        // Hitung total data untuk pagination
        $countSql = "SELECT COUNT(*) FROM documents d WHERE {$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();

        // Pengurutan
        $orderBy = match ($sort) {
            'date_asc'  => 'd.created_at ASC',
            'name_asc'  => 'd.title ASC',
            'name_desc' => 'd.title DESC',
            'size_desc' => 'd.file_size DESC',
            'size_asc'  => 'd.file_size ASC',
            default     => 'd.created_at DESC'
        };

        // Pagination
        $page = max($page, 1);
        $totalPages = max((int)ceil($totalItems / $perPage), 1);
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT d.*, c.name AS category_name
            FROM documents d
            JOIN categories c ON d.category_id = c.id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        return [
            'items'        => $items,
            'total_items'  => $totalItems,
            'current_page' => $page,
            'total_pages'  => $totalPages,
            'per_page'     => $perPage
        ];
    }

    /**
     * Memeriksa apakah pengguna berhak melihat/mengunduh dokumen
     * Aturan:
     * - Public: siapa saja boleh (termasuk anonim).
     * - Authenticated: semua pengguna yang login boleh.
     * - Private: HANYA pemilik dokumen yang boleh (admin tidak otomatis punya akses).
     */
    public function canAccess(array $document, ?array $currentUser): bool {
        // Dokumen yang dihapus (soft delete) tidak boleh diakses
        if (!empty($document['deleted_at'])) {
            return false;
        }

        $visibility = $document['visibility'];

        if ($visibility === 'public') {
            return true;
        }

        if ($currentUser === null) {
            return false;
        }

        if ($visibility === 'authenticated') {
            return true;
        }

        if ($visibility === 'private') {
            return (int)$currentUser['id'] === (int)$document['owner_id'];
        }

        return false;
    }

    /**
     * Ambil dokumen yang dapat dilihat oleh pengguna yang sedang login (visibility = authenticated ATAU public)
     * Ini untuk halaman "File Dibagikan"
     */
    public function getSharedDocuments(
        int $currentUserId,
        string $search = '',
        int $categoryId = 0,
        string $sort = 'date_desc',
        int $page = 1,
        int $perPage = 10
    ): array {
        $where = ["d.deleted_at IS NULL", "d.owner_id != ?", "d.visibility IN ('authenticated','public')"];
        $params = [$currentUserId];

        if ($search !== '') {
            $where[] = "(d.title LIKE ? OR d.description LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if ($categoryId > 0) {
            $where[] = "d.category_id = ?";
            $params[] = $categoryId;
        }

        $whereClause = implode(' AND ', $where);
        $orderBy = $this->buildOrderBy($sort);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM documents d WHERE {$whereClause}");
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();

        $page = max($page, 1);
        $totalPages = max((int)ceil($totalItems / $perPage), 1);
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT d.*, c.name AS category_name, u.name AS owner_name, u.institution AS owner_institution
            FROM documents d
            JOIN categories c ON d.category_id = c.id
            JOIN users u ON d.owner_id = u.id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'items'        => $stmt->fetchAll(),
            'total_items'  => $totalItems,
            'current_page' => $page,
            'total_pages'  => $totalPages,
            'per_page'     => $perPage
        ];
    }

    /**
     * Ambil dokumen publik saja (visibility = public) — untuk halaman Jelajahi, pengunjung anonim boleh lihat
     */
    public function getPublicDocuments(
        string $search = '',
        int $categoryId = 0,
        string $sort = 'date_desc',
        int $page = 1,
        int $perPage = 10
    ): array {
        $where = ["d.deleted_at IS NULL", "d.visibility = 'public'"];
        $params = [];

        if ($search !== '') {
            $where[] = "(d.title LIKE ? OR d.description LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if ($categoryId > 0) {
            $where[] = "d.category_id = ?";
            $params[] = $categoryId;
        }

        $whereClause = implode(' AND ', $where);
        $orderBy = $this->buildOrderBy($sort);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM documents d WHERE {$whereClause}");
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();

        $page = max($page, 1);
        $totalPages = max((int)ceil($totalItems / $perPage), 1);
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT d.*, c.name AS category_name, u.name AS owner_name, u.institution AS owner_institution
            FROM documents d
            JOIN categories c ON d.category_id = c.id
            JOIN users u ON d.owner_id = u.id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'items'        => $stmt->fetchAll(),
            'total_items'  => $totalItems,
            'current_page' => $page,
            'total_pages'  => $totalPages,
            'per_page'     => $perPage
        ];
    }

    private function buildOrderBy(string $sort): string {
        return match ($sort) {
            'date_asc'  => 'd.created_at ASC',
            'name_asc'  => 'd.title ASC',
            'name_desc' => 'd.title DESC',
            'size_desc' => 'd.file_size DESC',
            'size_asc'  => 'd.file_size ASC',
            default     => 'd.created_at DESC'
        };
    }
}
