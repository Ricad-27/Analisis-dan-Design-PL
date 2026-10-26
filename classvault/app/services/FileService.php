<?php
/**
 * ClassVault - FileService
 * Menangani validasi berkas upload, deteksi MIME server-side, penyimpanan aman, dan penghapusan berkas.
 */

require_once __DIR__ . '/../helpers/Security.php';

class FileService {
    // Ukuran maksimal 10 MB (10 * 1024 * 1024 bytes)
    public const MAX_SIZE_BYTES = 10485760;

    // Pemetaan ekstensi ke MIME type yang sah
    private static array $allowedMimes = [
        'pdf'  => ['application/pdf'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
        'txt'  => ['text/plain'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
    ];

    /**
     * Validasi berkas upload dari $_FILES
     * 
     * @return array [ 'success' => bool, 'error' => string|null, 'data' => array|null ]
     */
    public static function validateAndProcess(array $file): array {
        // 1. Periksa kode error PHP upload
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'error' => 'Parameter berkas tidak valid.'];
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'error' => 'Ukuran berkas melebihi batas maksimum 10 MB.'];
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'error' => 'Silakan pilih berkas yang ingin diunggah.'];
            case UPLOAD_ERR_PARTIAL:
                return ['success' => false, 'error' => 'Berkas hanya terunggah sebagian. Silakan coba kembali.'];
            default:
                return ['success' => false, 'error' => 'Terjadi kesalahan sistem saat mengunggah berkas.'];
        }

        // 2. Periksa apakah file fisik sementara ada
        $tmpPath = $file['tmp_name'] ?? '';
        if (!is_uploaded_file($tmpPath) || !file_exists($tmpPath)) {
            return ['success' => false, 'error' => 'Berkas sementara tidak ditemukan.'];
        }

        // 3. Periksa ukuran berkas (tidak boleh 0 byte dan tidak boleh > 10MB)
        $fileSize = (int)($file['size'] ?? 0);
        if ($fileSize <= 0) {
            return ['success' => false, 'error' => 'Berkas kosong (0 byte) tidak dapat diunggah.'];
        }
        if ($fileSize > self::MAX_SIZE_BYTES) {
            return ['success' => false, 'error' => 'Ukuran berkas melebihi batas maksimum 10 MB.'];
        }

        // 4. Periksa ekstensi file
        $originalName = Security::sanitizeOriginalFilename($file['name'] ?? 'dokumen');
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!array_key_exists($ext, self::$allowedMimes)) {
            return [
                'success' => false,
                'error' => "Format file .{$ext} tidak diizinkan. Format yang didukung: PDF, DOCX, XLSX, PPTX, TXT, JPG, PNG."
            ];
        }

        // Blokir eksplisit file berbahaya apapun
        $dangerousExts = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'cgi', 'exe', 'sh', 'bat', 'cmd', 'js', 'jar', 'vbs'];
        if (in_array($ext, $dangerousExts, true)) {
            return ['success' => false, 'error' => 'Format file berbahaya ditolak oleh sistem.'];
        }

        // 5. Deteksi MIME type di server menggunakan finfo (bukan dari browser)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        $validMimesForExt = self::$allowedMimes[$ext] ?? [];
        if (!in_array($detectedMime, $validMimesForExt, true)) {
            return [
                'success' => false,
                'error' => "Konten berkas ({$detectedMime}) tidak sesuai dengan ekstensi file .{$ext}."
            ];
        }

        // 6. Buat nama file penyimpanan internal acak
        $storageFilename = Security::generateStorageName($ext);
        $targetDirectory = STORAGE_PATH . DIRECTORY_SEPARATOR . 'uploads';

        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $storageFilename;

        // 7. Pindahkan berkas ke folder penyimpanan aman
        if (!move_uploaded_file($tmpPath, $targetPath)) {
            // Bersihkan file sementara jika gagal
            @unlink($tmpPath);
            return ['success' => false, 'error' => 'Gagal memindahkan berkas ke direktori penyimpanan aman.'];
        }

        return [
            'success' => true,
            'data'    => [
                'original_filename' => $originalName,
                'storage_filename'  => $storageFilename,
                'mime_type'         => $detectedMime,
                'file_size'         => $fileSize,
                'file_path'         => $targetPath,
            ]
        ];
    }

    /**
     * Hapus berkas fisik dari storage jika diperlukan (misal: saat update file revisi atau rollback)
     */
    public static function deletePhysicalFile(string $storageFilename): bool {
        // Cegah directory traversal
        $storageFilename = basename($storageFilename);
        $path = STORAGE_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $storageFilename;
        if (file_exists($path) && is_file($path)) {
            return @unlink($path);
        }
        return false;
    }
}
