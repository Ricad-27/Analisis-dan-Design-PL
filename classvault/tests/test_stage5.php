<?php
/**
 * ClassVault — Test Suite Tahap 5
 * Menguji: Jelajahi Dokumen, File Dibagikan, Profil, Ubah Password
 * Jalankan: php tests/test_stage5.php
 */

define('APP_PATH', __DIR__ . '/../app');
define('STORAGE_PATH', __DIR__ . '/../storage');
define('BASE_URL', 'http://localhost/classvault/public');

require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/config/database.php';
require_once APP_PATH . '/helpers/Security.php';
require_once APP_PATH . '/helpers/Session.php';
require_once APP_PATH . '/helpers/View.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Document.php';

// ─── Test runner ──────────────────────────────────────────────────────────────
$passed = 0;
$failed = 0;

function check(string $name, bool $condition, string $detail = ''): void {
    global $passed, $failed;
    $status = $condition ? ' [PASS]' : ' [FAIL]';
    if ($condition) { $passed++; } else { $failed++; }
    echo $status . " " . $name . ($detail ? " — $detail" : "") . PHP_EOL;
}

// ─── Setup & seed data ────────────────────────────────────────────────────────
$db = Database::getConnection();
$db->exec("SET foreign_key_checks = 0");

// Hapus data test sebelumnya
$db->exec("DELETE FROM documents WHERE title LIKE 'T5_TEST_%'");
$db->exec("DELETE FROM users WHERE nim_normalized LIKE 't5test%'");
$db->exec("SET foreign_key_checks = 1");

// Buat 2 user test
$userModel = new User();
$ownerId = $userModel->registerStudent([
    'name'          => 'T5 Owner',
    'institution'   => 'Universitas Test Lima',
    'nim'           => 'T5TEST001',
    'program_study' => 'Teknik Informatika',
    'email'         => 't5owner@test.com',
    'password'      => 'Password123!',
]);

$otherId = $userModel->registerStudent([
    'name'          => 'T5 Other',
    'institution'   => 'Universitas Test Lima',
    'nim'           => 'T5TEST002',
    'program_study' => 'Teknik Elektro',
    'email'         => 't5other@test.com',
    'password'      => 'Password456!',
]);

// Buat dokumen dengan berbagai visibilitas
$docModel = new Document();

$pubDocId = $docModel->create([
    'title'       => 'T5_TEST_Public',
    'description' => 'Dokumen publik untuk test',
    'category_id' => 1,
    'owner_id'    => $ownerId,
    'file_name'   => 't5_pub.pdf',
    'file_path'   => 'storage/uploads/t5_pub.pdf',
    'file_size'   => 1024,
    'file_extension' => 'pdf',
    'visibility'  => 'public',
]);

$authDocId = $docModel->create([
    'title'       => 'T5_TEST_Auth',
    'description' => 'Dokumen authenticated untuk test',
    'category_id' => 1,
    'owner_id'    => $ownerId,
    'file_name'   => 't5_auth.pdf',
    'file_path'   => 'storage/uploads/t5_auth.pdf',
    'file_size'   => 2048,
    'file_extension' => 'pdf',
    'visibility'  => 'authenticated',
]);

$privDocId = $docModel->create([
    'title'       => 'T5_TEST_Private',
    'description' => 'Dokumen private untuk test',
    'category_id' => 1,
    'owner_id'    => $ownerId,
    'file_name'   => 't5_priv.pdf',
    'file_path'   => 'storage/uploads/t5_priv.pdf',
    'file_size'   => 512,
    'file_extension' => 'pdf',
    'visibility'  => 'private',
]);

echo PHP_EOL . "=== PENGUJIAN TAHAP 5: JELAJAHI & DIBAGIKAN ===" . PHP_EOL . PHP_EOL;

// ─── TEST: getPublicDocuments ─────────────────────────────────────────────────
$result = $docModel->getPublicDocuments();
$titles = array_column($result['items'], 'title');
check('1. Dokumen public muncul di Jelajahi', in_array('T5_TEST_Public', $titles));
check('2. Dokumen authenticated TIDAK muncul di Jelajahi', !in_array('T5_TEST_Auth', $titles));
check('3. Dokumen private TIDAK muncul di Jelajahi', !in_array('T5_TEST_Private', $titles));
check('4. total_items minimal 1', $result['total_items'] >= 1);

// Filter kategori
$resultCat = $docModel->getPublicDocuments('', 999); // kategori tidak ada
check('5. Filter kategori tidak ada menghasilkan 0 dokumen', $resultCat['total_items'] === 0);

// Filter search
$resultSearch = $docModel->getPublicDocuments('T5_TEST_Public');
$searchTitles = array_column($resultSearch['items'], 'title');
check('6. Search "T5_TEST_Public" menemukan dokumen', in_array('T5_TEST_Public', $searchTitles));
$resultSearchEmpty = $docModel->getPublicDocuments('xyznotexist12345');
check('7. Search yang tidak cocok menghasilkan 0 dokumen', $resultSearchEmpty['total_items'] === 0);

// Pagination
$resultPage2 = $docModel->getPublicDocuments('', 0, 'date_desc', 999); // halaman 999 = kosong
check('8. Halaman yang melebihi total tetap mengembalikan array items', isset($resultPage2['items']));

// ─── TEST: getSharedDocuments ─────────────────────────────────────────────────
echo PHP_EOL . "=== PENGUJIAN: FILE DIBAGIKAN ===" . PHP_EOL . PHP_EOL;

// otherId (user lain) melihat file dibagikan oleh ownerId
$shared = $docModel->getSharedDocuments($otherId);
$sharedTitles = array_column($shared['items'], 'title');

check('9.  Dokumen public pemilik lain muncul di File Dibagikan', in_array('T5_TEST_Public', $sharedTitles));
check('10. Dokumen authenticated pemilik lain muncul di File Dibagikan', in_array('T5_TEST_Auth', $sharedTitles));
check('11. Dokumen private pemilik lain TIDAK muncul di File Dibagikan', !in_array('T5_TEST_Private', $sharedTitles));

// Dokumen milik ownerId sendiri TIDAK tampil di hasil otherId (ownerId=otherId diexclude sendiri)
// Coba dengan ownerId melihat file dibagikan — tidak boleh tampil dokumennya sendiri
$sharedSelf = $docModel->getSharedDocuments($ownerId);
$sharedSelfTitles = array_column($sharedSelf['items'], 'title');
check('12. Pemilik tidak melihat dokumennya sendiri di File Dibagikan',
    !in_array('T5_TEST_Public', $sharedSelfTitles) &&
    !in_array('T5_TEST_Auth', $sharedSelfTitles)
);

// Filter & search pada getSharedDocuments
$sharedSearch = $docModel->getSharedDocuments($otherId, 'T5_TEST_Auth');
$sharedSearchTitles = array_column($sharedSearch['items'], 'title');
check('13. Search di File Dibagikan berfungsi', in_array('T5_TEST_Auth', $sharedSearchTitles));

// ─── TEST: View::e() (XSS protection) ────────────────────────────────────────
echo PHP_EOL . "=== PENGUJIAN: VIEW HELPER ===" . PHP_EOL . PHP_EOL;

check('14. View::e() escape < dan >', View::e('<script>') === '&lt;script&gt;');
check('15. View::e() escape tanda petik ganda', View::e('"test"') === '&quot;test&quot;');
check('16. View::e() null mengembalikan string kosong', View::e(null) === '');

// ─── TEST: View::fileIcon() ───────────────────────────────────────────────────
check('17. View::fileIcon("pdf") mengembalikan emoji', View::fileIcon('pdf') !== '');
check('18. View::fileIcon("docx") berbeda dengan pdf', View::fileIcon('docx') !== View::fileIcon('pdf'));
check('19. View::formatSize() format angka besar', View::formatSize(1024 * 1024) === '1 MB' || strpos(View::formatSize(1024 * 1024), 'MB') !== false);

// ─── TEST: User::updateProfile() ─────────────────────────────────────────────
echo PHP_EOL . "=== PENGUJIAN: UPDATE PROFIL ===" . PHP_EOL . PHP_EOL;

$updated = $userModel->updateProfile($ownerId, [
    'name'          => 'T5 Owner Updated',
    'program_study' => 'Sistem Informasi',
    'email'         => 't5owner_new@test.com',
]);
check('20. updateProfile() mengembalikan true', $updated === true);

$updatedUser = $userModel->findById($ownerId);
check('21. Nama berhasil diperbarui', $updatedUser['name'] === 'T5 Owner Updated');
check('22. Program studi berhasil diperbarui', $updatedUser['program_study'] === 'Sistem Informasi');
check('23. Email berhasil diperbarui', $updatedUser['email'] === 't5owner_new@test.com');

// ─── TEST: User::updatePassword() ────────────────────────────────────────────
$newPw = 'NewSecureP@ss9';
$userModel->updatePassword($ownerId, $newPw);

// Verifikasi password baru dengan query langsung
$stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
$stmt->execute([$ownerId]);
$row = $stmt->fetch();
check('24. Password baru tersimpan dan terverifikasi via bcrypt',
    password_verify($newPw, $row['password_hash'])
);

// ─── TEST: Validasi duplikasi email pada update profil ────────────────────────
$emailTaken = $userModel->isEmailTaken('t5other@test.com', $ownerId);
check('25. Email milik pengguna lain terdeteksi sebagai sudah terpakai', $emailTaken === true);

$emailSelf = $userModel->isEmailTaken('t5owner_new@test.com', $ownerId);
check('26. Email milik diri sendiri TIDAK terdeteksi sebagai duplikat', $emailSelf === false);

// ─── TEST: Controller & View files exist ─────────────────────────────────────
echo PHP_EOL . "=== PENGUJIAN: KELENGKAPAN FILE ===" . PHP_EOL . PHP_EOL;

$files = [
    APP_PATH . '/controllers/ExploreController.php',
    APP_PATH . '/controllers/ProfileController.php',
    APP_PATH . '/views/explore/index.php',
    APP_PATH . '/views/explore/shared.php',
    APP_PATH . '/views/profile/index.php',
    APP_PATH . '/views/profile/change_password.php',
];
foreach ($files as $i => $f) {
    check(($i + 27) . '. File ' . basename($f) . ' ada dan tidak kosong',
        file_exists($f) && filesize($f) > 0
    );
}

// ─── TEST: Syntax PHP check semua file baru ────────────────────────────────────
echo PHP_EOL . "=== PENGUJIAN: SYNTAX PHP ===" . PHP_EOL . PHP_EOL;

$phpFiles = [
    APP_PATH . '/controllers/ExploreController.php',
    APP_PATH . '/controllers/ProfileController.php',
    APP_PATH . '/helpers/View.php',
    APP_PATH . '/models/Document.php',
    __DIR__ . '/../public/index.php',
];
foreach ($phpFiles as $i => $f) {
    $out = shell_exec("php -l \"$f\" 2>&1");
    $ok  = str_contains($out, 'No syntax errors detected');
    check(($i + 33) . '. Syntax ' . basename($f), $ok, $ok ? '' : trim($out));
}

// ─── Teardown ────────────────────────────────────────────────────────────────
$db->exec("DELETE FROM documents WHERE title LIKE 'T5_TEST_%'");
$db->exec("DELETE FROM users WHERE nim_normalized LIKE 't5test%'");

// ─── Summary ──────────────────────────────────────────────────────────────────
echo PHP_EOL . "═══════════════════════════════════════════" . PHP_EOL;
$total = $passed + $failed;
echo "HASIL AKHIR: {$passed} dari {$total} PASS";
if ($failed > 0) {
    echo " ({$failed} GAGAL)";
}
echo PHP_EOL . "═══════════════════════════════════════════" . PHP_EOL;
exit($failed > 0 ? 1 : 0);
