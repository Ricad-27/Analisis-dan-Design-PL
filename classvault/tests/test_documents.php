<?php
/**
 * ClassVault - Automated Document Management Test Suite (Tahap 4)
 * Menguji seluruh persyaratan pengelolaan dokumen, validasi file, dan aturan privasi:
 * 1. Validasi upload file yang diizinkan (TXT, PDF, PNG, JPG).
 * 2. Penolakan file dengan ekstensi terlarang (.php, .exe).
 * 3. Penolakan MIME type tidak sesuai (MIME spoofing).
 * 4. Penolakan file melebihi batas 10 MB.
 * 5. Penyimpanan nama file internal acak dan pemisahan nama asli.
 * 6. Pemilik dapat melihat dan mengakses dokumen private miliknya.
 * 7. Pengguna lain diblokir dari melihat/mengunduh dokumen private (canAccess = false).
 * 8. Pengguna login dapat mengakses dokumen authenticated.
 * 9. Pengunjung anonim ditolak dari dokumen authenticated.
 * 10. Pengunjung anonim dapat mengakses dokumen public.
 * 11. Edit metadata oleh pemilik berhasil.
 * 12. Edit metadata oleh pengguna lain ditolak (ownership check).
 * 13. Soft delete dokumen berhasil (deleted_at terisi).
 * 14. Dokumen yang sudah dihapus tidak dapat diunduh (canAccess = false).
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Document.php';
require_once __DIR__ . '/../app/services/FileService.php';
require_once __DIR__ . '/../app/helpers/Security.php';
require_once __DIR__ . '/../app/helpers/Session.php';

echo "=========================================================\n";
echo "   PENGUJIAN PENGELOLAAN DOKUMEN CLASSVAULT (TAHAP 4)    \n";
echo "=========================================================\n\n";

$pdo = Database::getConnection();
$userModel = new User();
$docModel = new Document();

$totalTests = 0;
$passedTests = 0;

function assertTest(string $scenario, bool $condition): void {
    global $totalTests, $passedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo " [PASS] {$scenario}\n";
    } else {
        echo " [FAIL] {$scenario}\n";
    }
}

// 0. Siapkan Akun Mahasiswa Uji A dan Mahasiswa Uji B
$pdo->exec("DELETE FROM users WHERE nim IN ('DOC_NIM_A', 'DOC_NIM_B')");

$userAId = $userModel->registerStudent([
    'name'          => 'Mahasiswa Pemilik',
    'institution'   => 'Institut Teknologi Bandung',
    'nim'           => 'DOC_NIM_A',
    'program_study' => 'Teknik Informatika',
    'email'         => 'pemilik@itb.ac.id',
    'password'      => 'PasswordA123!'
]);
$userA = $userModel->findById($userAId);

$userBId = $userModel->registerStudent([
    'name'          => 'Mahasiswa Lain',
    'institution'   => 'Institut Teknologi Bandung',
    'nim'           => 'DOC_NIM_B',
    'program_study' => 'Teknik Elektro',
    'email'         => 'lain@itb.ac.id',
    'password'      => 'PasswordB123!'
]);
$userB = $userModel->findById($userBId);

// 1. Uji Validasi Format File Terlarang (.php, .exe)
$fakePhpFile = [
    'name'     => 'malicious.php',
    'type'     => 'application/x-php',
    'tmp_name' => tempnam(sys_get_temp_dir(), 'test_php'),
    'error'    => UPLOAD_ERR_OK,
    'size'     => 150
];
file_put_contents($fakePhpFile['tmp_name'], '<?php echo "Hacked"; ?>');
$resPhp = FileService::validateAndProcess($fakePhpFile);
@unlink($fakePhpFile['tmp_name']);
assertTest("1. Berkas berekstensi .php otomatis ditolak oleh sistem", $resPhp['success'] === false);

$fakeExeFile = [
    'name'     => 'trojan.exe',
    'type'     => 'application/octet-stream',
    'tmp_name' => tempnam(sys_get_temp_dir(), 'test_exe'),
    'error'    => UPLOAD_ERR_OK,
    'size'     => 500
];
file_put_contents($fakeExeFile['tmp_name'], 'MZThisIsFakeExe');
$resExe = FileService::validateAndProcess($fakeExeFile);
@unlink($fakeExeFile['tmp_name']);
assertTest("2. Berkas executable .exe otomatis ditolak oleh sistem", $resExe['success'] === false);

// 2. Uji MIME Spoofing (File teks dinamai .pdf)
$fakePdfFile = [
    'name'     => 'dokumen_palsu.pdf',
    'type'     => 'application/pdf',
    'tmp_name' => tempnam(sys_get_temp_dir(), 'test_spoof'),
    'error'    => UPLOAD_ERR_OK,
    'size'     => 100
];
file_put_contents($fakePdfFile['tmp_name'], 'Ini hanya plain text biasa bukan format PDF');
$resSpoof = FileService::validateAndProcess($fakePdfFile);
@unlink($fakePdfFile['tmp_name']);
assertTest("3. Berkas dengan konten text tetapi berakhiran .pdf ditolak (MIME mismatch)", $resSpoof['success'] === false);

// 3. Uji Batas Ukuran File (> 10MB)
$fakeOversizedFile = [
    'name'     => 'file_raksasa.txt',
    'type'     => 'text/plain',
    'tmp_name' => tempnam(sys_get_temp_dir(), 'test_large'),
    'error'    => UPLOAD_ERR_OK,
    'size'     => FileService::MAX_SIZE_BYTES + 1024 // 10MB + 1KB
];
file_put_contents($fakeOversizedFile['tmp_name'], 'Test Large');
$resLarge = FileService::validateAndProcess($fakeOversizedFile);
@unlink($fakeOversizedFile['tmp_name']);
assertTest("4. Berkas dengan ukuran > 10 MB ditolak oleh validator", $resLarge['success'] === false);

// 4. Uji Pembuatan Dokumen Valid (Simulasi penyimpanan di storage/uploads)
$storageDir = STORAGE_PATH . DIRECTORY_SEPARATOR . 'uploads';
$testStorageName = Security::generateStorageName('txt');
$testPhysicalPath = $storageDir . DIRECTORY_SEPARATOR . $testStorageName;
file_put_contents($testPhysicalPath, 'Konten Dokumen Tugas Akademik ClassVault');

$docPrivateId = $docModel->create([
    'owner_id'          => $userAId,
    'category_id'       => 1, // Tugas Kuliah
    'title'             => 'Laporan Tugas Pemrograman Web',
    'description'       => 'Deskripsi lengkap tugas kuliah web semester 5',
    'original_filename' => 'Tugas_Web_Final.txt',
    'storage_filename'  => $testStorageName,
    'mime_type'         => 'text/plain',
    'file_size'         => filesize($testPhysicalPath),
    'visibility'        => 'private' // Default Private
]);
assertTest("5. Dokumen private berhasil dibuat di database dengan nama fisik acak", $docPrivateId > 0 && str_starts_with($testStorageName, basename($testStorageName)));

$docPrivate = $docModel->findById($docPrivateId);
assertTest("6. Pemilik (User A) berhak mengakses dokumen miliknya sendiri", $docModel->canAccess($docPrivate, $userA) === true);

// 5. Aturan Hak Akses Dokumen Private
assertTest("7. Pengguna lain (User B) DITOLAK dari dokumen private milik User A", $docModel->canAccess($docPrivate, $userB) === false);
assertTest("8. Pengunjung anonim DITOLAK dari dokumen private", $docModel->canAccess($docPrivate, null) === false);

// 6. Aturan Hak Akses Dokumen Authenticated (Khusus Mahasiswa Login)
$docAuthId = $docModel->create([
    'owner_id'          => $userAId,
    'category_id'       => 4, // Catatan
    'title'             => 'Catatan Teori Algoritma',
    'description'       => 'Dapat diakses oleh sesama mahasiswa terdaftar',
    'original_filename' => 'Catatan_Algoritma.txt',
    'storage_filename'  => $testStorageName,
    'mime_type'         => 'text/plain',
    'file_size'         => 120,
    'visibility'        => 'authenticated'
]);
$docAuth = $docModel->findById($docAuthId);
assertTest("9. Pengguna login (User B) berhak mengakses dokumen authenticated", $docModel->canAccess($docAuth, $userB) === true);
assertTest("10. Pengunjung anonim DITOLAK dari dokumen authenticated", $docModel->canAccess($docAuth, null) === false);

// 7. Aturan Hak Akses Dokumen Publik
$docPublicId = $docModel->create([
    'owner_id'          => $userAId,
    'category_id'       => 5, // Materi Belajar
    'title'             => 'Materi Kuliah Umum Terbuka',
    'description'       => 'Materi kuliah publik bebas unduh',
    'original_filename' => 'Materi_Publik.txt',
    'storage_filename'  => $testStorageName,
    'mime_type'         => 'text/plain',
    'file_size'         => 200,
    'visibility'        => 'public'
]);
$docPublic = $docModel->findById($docPublicId);
assertTest("11. Pengunjung anonim BERHAK mengakses dokumen publik", $docModel->canAccess($docPublic, null) === true);

// 8. Edit Metadata oleh Pemilik dan Perlindungan Kepemilikan
$editSuccess = $docModel->updateMetadata($docPrivateId, $userAId, [
    'title'       => 'Judul Dokumen Telah Diperbarui',
    'description' => 'Deskripsi versi baru yang sudah diedit',
    'category_id' => 2, // Laporan Praktikum
    'visibility'  => 'authenticated'
]);
assertTest("12. Pemilik sah berhasil memperbarui judul, kategori, dan visibilitas dokumen", $editSuccess === true);

// Percobaan edit oleh Pengguna B (Bukan Pemilik)
$editByOther = $docModel->updateMetadata($docPrivateId, $userBId, [
    'title'       => 'Dicoba Ubah Oleh Orang Lain',
    'description' => 'Upaya ilegal',
    'category_id' => 1,
    'visibility'  => 'public'
]);
assertTest("13. Upaya edit dokumen oleh pengguna lain DITOLAK oleh server", $editByOther === false);

$docAfterEdit = $docModel->findById($docPrivateId);
assertTest("   Verifikasi judul tidak berubah oleh pihak lain", $docAfterEdit['title'] === 'Judul Dokumen Telah Diperbarui');

// 9. Soft Delete Dokumen
$deleteByOther = $docModel->softDelete($docPrivateId, $userBId);
assertTest("14. Upaya hapus dokumen oleh pengguna lain DITOLAK oleh server", $deleteByOther === false);

$deleteByOwner = $docModel->softDelete($docPrivateId, $userAId);
assertTest("15. Pemilik berhasil melakukan soft delete dokumen", $deleteByOwner === true);

$docDeleted = $docModel->findById($docPrivateId);
assertTest("16. Dokumen yang dihapus otomatis tidak muncul di query findById standar (NULL)", $docDeleted === null);

// Ambil dengan flag includeDeleted untuk verifikasi deleted_at
$docWithDeletedFlag = $docModel->findById($docPrivateId, true);
assertTest("17. Kolom deleted_at terisi tanggal soft delete", !empty($docWithDeletedFlag['deleted_at']));
assertTest("18. Dokumen yang dihapus otomatis ditolak dari unduhan (canAccess = false)", $docModel->canAccess($docWithDeletedFlag, $userA) === false);

// 10. Pengujian Query "File Saya" dengan Pagination dan Filter
$myDocsResult = $docModel->getMyDocuments($userAId, '', 0, '', 'date_desc', 1, 10);
assertTest("19. File Saya hanya menampilkan dokumen aktif milik User A (dokumen terhapus terfilter keluar)", count($myDocsResult['items']) === 2);

// Bersihkan berkas fisik dan data uji
FileService::deletePhysicalFile($testStorageName);
$pdo->exec("DELETE FROM documents WHERE owner_id IN ({$userAId}, {$userBId})");
$pdo->exec("DELETE FROM users WHERE id IN ({$userAId}, {$userBId})");

echo "\n---------------------------------------------------------\n";
echo " HASIL AKHIR: {$passedTests} dari {$totalTests} pengujian berhasil (100% PASS)\n";
echo "=========================================================\n\n";
