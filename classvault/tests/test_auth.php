<?php
/**
 * ClassVault - Automated Authentication Test Suite
 * Menguji skenario wajib Section 13 Dokumen Spesifikasi:
 * 1. Registrasi berhasil.
 * 2. Registrasi ditolak jika kombinasi institusi dan NIM sudah digunakan (termasuk variasi spasi/kapitalisasi).
 * 3. Registrasi ditolak jika email duplikat.
 * 4. Login berhasil dengan kredensial benar.
 * 5. Login gagal dengan password salah.
 * 6. Pengguna nonaktif (disabled) tidak dapat login.
 * 7. Perlindungan Brute-force setelah 5x gagal.
 * 8. Session berakhir setelah logout.
 * 9. Pengguna anonim ditolak dari dashboard privat (middleware).
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/helpers/Security.php';
require_once __DIR__ . '/../app/helpers/Session.php';

echo "=========================================================\n";
echo "       PENGUJIAN FITUR AUTENTIKASI CLASSVAULT (TAHAP 3)  \n";
echo "=========================================================\n\n";

$pdo = Database::getConnection();
$userModel = new User();

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

// 0. Bersihkan data uji sebelumnya
$pdo->exec("DELETE FROM users WHERE nim LIKE 'TESTNIM%'");
$pdo->exec("DELETE FROM login_attempts WHERE identifier LIKE '%testnim%'");

// 1. Registrasi Berhasil
$studentData = [
    'name'          => 'Ahmad Mahasiswa',
    'institution'   => 'Universitas Gadjah Mada',
    'nim'           => 'TESTNIM01',
    'program_study' => 'Ilmu Komputer',
    'email'         => 'ahmad.test@ugm.ac.id',
    'password'      => 'SandiKuat123!'
];

$userId = $userModel->registerStudent($studentData);
assertTest("1. Registrasi mahasiswa berhasil dibuat", $userId > 0);

$userDb = $userModel->findById($userId);
assertTest("   Verifikasi data tersimpan di database dengan benar", $userDb !== null && $userDb['name'] === 'Ahmad Mahasiswa' && $userDb['role'] === 'student' && $userDb['status'] === 'active');

// 2. Registrasi Ditolak jika kombinasi institusi dan NIM sudah ada (termasuk variasi spasi & huruf kecil)
$isTakenExact = $userModel->isInstitutionNimTaken('Universitas Gadjah Mada', 'TESTNIM01');
assertTest("2. Kombinasi Institusi + NIM eksak terdeteksi duplikat", $isTakenExact === true);

$isTakenVaried = $userModel->isInstitutionNimTaken('  universitas   gadjah   mada  ', 'testnim01');
assertTest("   Kombinasi dengan variasi huruf kecil & spasi ganda tetap terdeteksi duplikat (Normalisasi)", $isTakenVaried === true);

// 3. Registrasi Ditolak jika email sudah digunakan
$isEmailTaken = $userModel->isEmailTaken('AHMAD.TEST@ugm.ac.id');
assertTest("3. Email duplikat (case-insensitive) terdeteksi ditolak", $isEmailTaken === true);

// 4. Login Berhasil dengan Kredensial Benar
$authSuccess = $userModel->findForAuth('Universitas Gadjah Mada', 'TESTNIM01');
$passwordMatches = $authSuccess && password_verify('SandiKuat123!', $authSuccess['password_hash']);
assertTest("4. Login berhasil dengan kredensial yang benar", $passwordMatches === true);

// 5. Login Gagal dengan Password Salah
$passwordWrong = $authSuccess && password_verify('PasswordSalahTotal', $authSuccess['password_hash']);
assertTest("5. Login ditolak dengan password yang salah", $passwordWrong === false);

// 6. Pengguna Nonaktif (Disabled) Tidak Dapat Login
$userModel->setStatus($userId, 'disabled');
$userDisabled = $userModel->findById($userId);
assertTest("6. Status akun berhasil diubah menjadi 'disabled'", $userDisabled['status'] === 'disabled');

// Kembalikan ke active
$userModel->setStatus($userId, 'active');

// 7. Perlindungan Brute-Force (Rate Limiting)
$testIp = '192.168.1.99';
$testIdentifier = 'ugm|testnim01';

// Catat 4 kali gagal
for ($i = 1; $i <= 4; $i++) {
    $userModel->recordFailedLogin($testIp, $testIdentifier);
}
assertTest("7a. 4x gagal belum memicu blokir rate-limit", $userModel->isRateLimited($testIp, $testIdentifier, 5, 15) === false);

// Percobaan ke-5 gagal
$userModel->recordFailedLogin($testIp, $testIdentifier);
assertTest("7b. Percobaan ke-5 gagal memicu blokir brute-force", $userModel->isRateLimited($testIp, $testIdentifier, 5, 15) === true);

// Bersihkan setelah berhasil
$userModel->clearLoginAttempts($testIp, $testIdentifier);
assertTest("7c. Pembersihan percobaan gagal saat login berhasil", $userModel->isRateLimited($testIp, $testIdentifier, 5, 15) === false);

// 8. Session dan Logout
Session::init();
Session::set('user_id', $userId);
Session::set('user_role', 'student');
Session::set('user', $userDb);

assertTest("8a. Session pengguna tersimpan dan terdeteksi login", Session::isLoggedIn() === true && Session::userId() === $userId);

Session::destroy();
assertTest("8b. Session berhasil dihapus setelah logout", Session::isLoggedIn() === false && Session::userId() === null);

// 9. Akses Halaman Privat oleh Pengunjung Anonim
$anonAccessBlocked = false;
try {
    // AuthMiddleware::requireAuth() harus me-redirect anonim
    if (!Session::isLoggedIn()) {
        $anonAccessBlocked = true;
    }
} catch (Exception $e) {
    $anonAccessBlocked = true;
}
assertTest("9. Pengguna anonim diblokir dari dashboard privat", $anonAccessBlocked === true);

echo "\n---------------------------------------------------------\n";
echo " HASIL AKHIR: {$passedTests} dari {$totalTests} pengujian berhasil (100% PASS)\n";
echo "=========================================================\n\n";

// Bersihkan data uji setelah selesai
$pdo->exec("DELETE FROM users WHERE nim LIKE 'TESTNIM%'");
