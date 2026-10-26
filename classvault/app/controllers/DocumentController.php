<?php
/**
 * ClassVault - DocumentController
 * Menangani upload dokumen, daftar "File Saya", detail, edit metadata, unduhan terproteksi, dan soft delete.
 */

require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../services/FileService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Security.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/View.php';

class DocumentController {
    private Document $documentModel;

    public function __construct() {
        $this->documentModel = new Document();
    }

    /**
     * Tampilkan halaman "File Saya" milik mahasiswa yang sedang login
     */
    public function myDocuments(): void {
        $user = AuthMiddleware::requireStudent();

        $search = trim($_GET['q'] ?? '');
        $categoryId = (int)($_GET['category'] ?? 0);
        $visibility = trim($_GET['visibility'] ?? '');
        $sort = trim($_GET['sort'] ?? 'date_desc');
        $page = (int)($_GET['page'] ?? 1);

        $categories = $this->documentModel->getCategories();
        $result = $this->documentModel->getMyDocuments(
            (int)$user['id'],
            $search,
            $categoryId,
            $visibility,
            $sort,
            $page,
            10 // 10 dokumen per halaman
        );

        View::render('student/documents/index', [
            'page_title' => 'File Saya',
            'user'       => $user,
            'documents'  => $result['items'],
            'pagination' => $result,
            'categories' => $categories,
            'filters'    => [
                'search'     => $search,
                'category'   => $categoryId,
                'visibility' => $visibility,
                'sort'       => $sort,
            ]
        ]);
    }

    /**
     * Formulir unggah dokumen baru
     */
    public function showUpload(): void {
        $user = AuthMiddleware::requireStudent();
        $categories = $this->documentModel->getCategories();

        View::render('student/documents/upload', [
            'page_title' => 'Unggah Dokumen',
            'user'       => $user,
            'categories' => $categories,
            'errors'     => [],
            'old'        => [
                'visibility' => 'private' // Default wajib Private sesuai spesifikasi Bagian 5
            ]
        ]);
    }

    /**
     * Proses unggah dokumen baru
     */
    public function upload(): void {
        $user = AuthMiddleware::requireStudent();
        $categories = $this->documentModel->getCategories();

        // Validasi CSRF
        if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            View::render('student/documents/upload', [
                'page_title' => 'Unggah Dokumen',
                'user'       => $user,
                'categories' => $categories,
                'errors'     => ['Sesi form telah kedaluwarsa. Silakan ulangi pengunggahan.'],
                'old'        => $_POST
            ]);
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $visibility = trim($_POST['visibility'] ?? 'private');

        $errors = [];

        if (empty($title)) {
            $errors[] = 'Nama / Judul dokumen wajib diisi.';
        } elseif (mb_strlen($title) < 3) {
            $errors[] = 'Judul dokumen minimal 3 karakter.';
        }

        if ($categoryId <= 0) {
            $errors[] = 'Silakan pilih kategori dokumen yang sesuai.';
        }

        if (!in_array($visibility, ['private', 'authenticated', 'public'], true)) {
            $visibility = 'private'; // Fallback aman
        }

        // Validasi dan pemrosesan berkas
        $uploadResult = FileService::validateAndProcess($_FILES['file'] ?? []);
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['error'];
        }

        if (!empty($errors)) {
            // Jika berkas sempat terpindah tapi ada error validasi lain, hapus
            if (!empty($uploadResult['data']['storage_filename'])) {
                FileService::deletePhysicalFile($uploadResult['data']['storage_filename']);
            }

            View::render('student/documents/upload', [
                'page_title' => 'Unggah Dokumen',
                'user'       => $user,
                'categories' => $categories,
                'errors'     => $errors,
                'old'        => [
                    'title'       => $title,
                    'category_id' => $categoryId,
                    'description' => $description,
                    'visibility'  => $visibility
                ]
            ]);
            return;
        }

        $fileData = $uploadResult['data'];

        // Simpan data ke basis data
        try {
            $docId = $this->documentModel->create([
                'owner_id'          => (int)$user['id'],
                'category_id'       => $categoryId,
                'title'             => $title,
                'description'       => $description,
                'original_filename' => $fileData['original_filename'],
                'storage_filename'  => $fileData['storage_filename'],
                'mime_type'         => $fileData['mime_type'],
                'file_size'         => $fileData['file_size'],
                'visibility'        => $visibility
            ]);

            View::redirect('/my-documents', 'success', 'Dokumen "' . Security::e($title) . '" berhasil diunggah.');
        } catch (Exception $e) {
            error_log('Database Insert Document Error: ' . $e->getMessage());
            FileService::deletePhysicalFile($fileData['storage_filename']);

            View::render('student/documents/upload', [
                'page_title' => 'Unggah Dokumen',
                'user'       => $user,
                'categories' => $categories,
                'errors'     => ['Terjadi kesalahan basis data saat menyimpan informasi dokumen.'],
                'old'        => $_POST
            ]);
        }
    }

    /**
     * Tampilkan detail dokumen
     */
    public function detail(string $id): void {
        $docId = (int)$id;
        $doc = $this->documentModel->findById($docId);

        if (!$doc) {
            http_response_code(404);
            View::render('errors/404', [
                'page_title' => 'Dokumen Tidak Ditemukan'
            ]);
            return;
        }

        $currentUser = Session::user();

        // Pemeriksaan Hak Akses
        if (!$this->documentModel->canAccess($doc, $currentUser)) {
            // Jika dokumen berstatus 'authenticated' dan pengunjung belum login, arahkan ke login
            if ($doc['visibility'] === 'authenticated' && $currentUser === null) {
                View::redirect('/login', 'warning', 'Dokumen ini hanya dapat diakses oleh pengguna yang sudah masuk.');
            }

            http_response_code(403);
            View::render('errors/403', [
                'page_title' => '403 - Akses Dokumen Ditolak',
                'message'    => 'Anda tidak memiliki hak akses untuk melihat dokumen ini.'
            ]);
            return;
        }

        $isOwner = $currentUser && (int)$currentUser['id'] === (int)$doc['owner_id'];

        View::render('student/documents/detail', [
            'page_title' => $doc['title'] . ' - Detail Dokumen',
            'doc'        => $doc,
            'is_owner'   => $isOwner
        ]);
    }

    /**
     * Formulir edit metadata dokumen
     */
    public function showEdit(string $id): void {
        $user = AuthMiddleware::requireStudent();
        $docId = (int)$id;
        $doc = $this->documentModel->findById($docId);

        if (!$doc) {
            http_response_code(404);
            View::render('errors/404', ['page_title' => 'Dokumen Tidak Ditemukan']);
            return;
        }

        // Periksa kepemilikan di sisi server
        if ((int)$doc['owner_id'] !== (int)$user['id']) {
            http_response_code(403);
            View::render('errors/403', [
                'page_title' => '403 - Akses Ditolak',
                'message'    => 'Anda tidak berhak mengedit dokumen milik pengguna lain.'
            ]);
            return;
        }

        $categories = $this->documentModel->getCategories();

        View::render('student/documents/edit', [
            'page_title' => 'Edit Metadata Dokumen',
            'user'       => $user,
            'doc'        => $doc,
            'categories' => $categories,
            'errors'     => []
        ]);
    }

    /**
     * Proses pembaruan metadata dokumen
     */
    public function edit(string $id): void {
        $user = AuthMiddleware::requireStudent();
        $docId = (int)$id;
        $doc = $this->documentModel->findById($docId);

        if (!$doc) {
            http_response_code(404);
            View::render('errors/404', ['page_title' => 'Dokumen Tidak Ditemukan']);
            return;
        }

        // Verifikasi kepemilikan
        if ((int)$doc['owner_id'] !== (int)$user['id']) {
            http_response_code(403);
            View::render('errors/403', [
                'page_title' => '403 - Akses Ditolak',
                'message'    => 'Anda tidak berhak mengubah metadata dokumen milik pengguna lain.'
            ]);
            return;
        }

        $categories = $this->documentModel->getCategories();

        if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            View::render('student/documents/edit', [
                'page_title' => 'Edit Metadata Dokumen',
                'user'       => $user,
                'doc'        => $doc,
                'categories' => $categories,
                'errors'     => ['Sesi form telah berakhir. Silakan muat ulang dan coba lagi.']
            ]);
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $visibility = trim($_POST['visibility'] ?? 'private');

        $errors = [];

        if (empty($title)) {
            $errors[] = 'Judul dokumen wajib diisi.';
        } elseif (mb_strlen($title) < 3) {
            $errors[] = 'Judul dokumen minimal 3 karakter.';
        }

        if ($categoryId <= 0) {
            $errors[] = 'Silakan pilih kategori dokumen.';
        }

        if (!in_array($visibility, ['private', 'authenticated', 'public'], true)) {
            $visibility = $doc['visibility'];
        }

        // Periksa apakah pemilik mengunggah file revisi pengganti
        $hasRevisionFile = isset($_FILES['revision_file']) &&
                           ($_FILES['revision_file']['error'] !== UPLOAD_ERR_NO_FILE);
        
        $newFileData = null;
        if ($hasRevisionFile) {
            $uploadResult = FileService::validateAndProcess($_FILES['revision_file']);
            if (!$uploadResult['success']) {
                $errors[] = $uploadResult['error'];
            } else {
                $newFileData = $uploadResult['data'];
            }
        }

        if (!empty($errors)) {
            if ($newFileData) {
                FileService::deletePhysicalFile($newFileData['storage_filename']);
            }

            View::render('student/documents/edit', [
                'page_title' => 'Edit Metadata Dokumen',
                'user'       => $user,
                'doc'        => array_merge($doc, [
                    'title'       => $title,
                    'category_id' => $categoryId,
                    'description' => $description,
                    'visibility'  => $visibility
                ]),
                'categories' => $categories,
                'errors'     => $errors
            ]);
            return;
        }

        $updatePayload = [
            'title'       => $title,
            'category_id' => $categoryId,
            'description' => $description,
            'visibility'  => $visibility,
        ];

        if ($newFileData) {
            $updatePayload['original_filename'] = $newFileData['original_filename'];
            $updatePayload['storage_filename']  = $newFileData['storage_filename'];
            $updatePayload['mime_type']         = $newFileData['mime_type'];
            $updatePayload['file_size']         = $newFileData['file_size'];
        }

        $updated = $this->documentModel->updateMetadata($docId, (int)$user['id'], $updatePayload);

        if ($updated) {
            // Jika berkas revisi baru berhasil tersimpan, hapus berkas fisik lama
            if ($newFileData) {
                FileService::deletePhysicalFile($doc['storage_filename']);
            }

            View::redirect("/document/{$docId}", 'success', 'Metadata dokumen berhasil diperbarui.');
        } else {
            if ($newFileData) {
                FileService::deletePhysicalFile($newFileData['storage_filename']);
            }

            View::render('student/documents/edit', [
                'page_title' => 'Edit Metadata Dokumen',
                'user'       => $user,
                'doc'        => $doc,
                'categories' => $categories,
                'errors'     => ['Gagal memperbarui metadata dokumen di basis data.']
            ]);
        }
    }

    /**
     * Soft delete dokumen oleh pemilik
     */
    public function delete(string $id): void {
        $user = AuthMiddleware::requireStudent();
        $docId = (int)$id;

        if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            View::redirect('/my-documents', 'danger', 'Token keamanan CSRF tidak valid.');
        }

        $doc = $this->documentModel->findById($docId);
        if (!$doc) {
            View::redirect('/my-documents', 'danger', 'Dokumen tidak ditemukan atau sudah dihapus.');
        }

        if ((int)$doc['owner_id'] !== (int)$user['id']) {
            http_response_code(403);
            View::render('errors/403', [
                'page_title' => '403 - Akses Ditolak',
                'message'    => 'Anda hanya berhak menghapus dokumen milik Anda sendiri.'
            ]);
            return;
        }

        $deleted = $this->documentModel->softDelete($docId, (int)$user['id']);

        if ($deleted) {
            View::redirect('/my-documents', 'success', 'Dokumen "' . Security::e($doc['title']) . '" berhasil dihapus.');
        } else {
            View::redirect('/my-documents', 'danger', 'Gagal menghapus dokumen.');
        }
    }

    /**
     * Endpoint unduhan terproteksi
     * Memeriksa otentikasi, hak akses kepemilikan, dan aturan visibilitas
     */
    public function download(string $id): void {
        $docId = (int)$id;
        $doc = $this->documentModel->findById($docId);

        // Dokumen tidak ditemukan atau berstatus soft-deleted
        if (!$doc || !empty($doc['deleted_at'])) {
            http_response_code(404);
            View::render('errors/404', [
                'page_title' => 'Berkas Tidak Ditemukan'
            ]);
            return;
        }

        $currentUser = Session::user();

        // Pemeriksaan Hak Akses
        if (!$this->documentModel->canAccess($doc, $currentUser)) {
            // Jika visibilitas butuh login dan pengunjung belum masuk
            if ($doc['visibility'] === 'authenticated' && $currentUser === null) {
                View::redirect('/login', 'warning', 'Silakan masuk terlebih dahulu untuk mengunduh dokumen ini.');
            }

            http_response_code(403);
            View::render('errors/403', [
                'page_title' => '403 - Unduhan Ditolak',
                'message'    => 'Anda tidak memiliki hak akses untuk mengunduh dokumen ini.'
            ]);
            return;
        }

        // Lokasi fisik berkas di server
        $storageFilename = basename($doc['storage_filename']);
        $filePath = STORAGE_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $storageFilename;

        if (!file_exists($filePath) || !is_file($filePath)) {
            http_response_code(404);
            View::render('errors/404', [
                'page_title' => 'Berkas Fisik Tidak Ditemukan',
                'message'    => 'Berkas fisik tidak ditemukan di sistem penyimpanan.'
            ]);
            return;
        }

        // Cegah eksekusi script atau manipulasi header
        if (ob_get_level()) {
            ob_end_clean();
        }

        $cleanFilename = Security::sanitizeOriginalFilename($doc['original_filename']);
        $encodedFilename = rawurlencode($cleanFilename);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($doc['mime_type'] ?: 'application/octet-stream'));
        header("Content-Disposition: attachment; filename=\"{$cleanFilename}\"; filename*=UTF-8''{$encodedFilename}");
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        // Kirim berkas dalam chunks agar efisien pada memori
        $handle = fopen($filePath, 'rb');
        if ($handle) {
            while (!feof($handle)) {
                echo fread($handle, 8192);
                flush();
            }
            fclose($handle);
        }
        exit;
    }
}
