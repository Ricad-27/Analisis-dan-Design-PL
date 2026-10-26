<?php
/**
 * ClassVault - CLI Admin Account Creator
 * Digunakan untuk membuat akun administrator pertama secara aman.
 * Menghindari penanaman kredensial tetap di dalam source code.
 * 
 * Penggunaan:
 *   php bin/create_admin.php
 *   atau:
 *   php bin/create_admin.php "Nama Admin" "Institusi" "NIM/NIP" "Prodi" "admin@example.com" "password123"
 */

if (php_sapi_name() !== 'cli') {
    die("Perintah ini hanya dapat dijalankan melalui Terminal CLI.\n");
}

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/Security.php';

echo "====================================================\n";
echo "   ClassVault - Pembuatan Akun Administrator CLI    \n";
echo "====================================================\n\n";

$args = array_slice($argv, 1);

if (count($args) >= 6) {
    $name = trim($args[0]);
    $institution = trim($args[1]);
    $nim = trim($args[2]);
    $program_study = trim($args[3]);
    $email = trim($args[4]);
    $password = $args[5];
} else {
    // Mode Interaktif
    echo "Nama Lengkap: ";
    $name = trim(fgets(STDIN));

    echo "Institusi / Universitas: ";
    $institution = trim(fgets(STDIN));

    echo "NIM / NIP / ID Admin: ";
    $nim = trim(fgets(STDIN));

    echo "Program Studi / Departemen: ";
    $program_study = trim(fgets(STDIN));

    echo "Email (opsional, tekan Enter jika kosong): ";
    $email = trim(fgets(STDIN));

    echo "Password (min 8 karakter): ";
    $password = trim(fgets(STDIN));
}

// Validasi input
if (empty($name) || empty($institution) || empty($nim) || empty($program_study) || empty($password)) {
    die("Error: Semua bidang wajib (kecuali email) harus diisi!\n");
}

if (strlen($password) < 8) {
    die("Error: Password minimal harus 8 karakter!\n");
}

$institutionNorm = Security::normalize($institution);
$nimNorm = Security::normalize($nim);
$emailVal = !empty($email) ? filter_var($email, FILTER_VALIDATE_EMAIL) : null;

if (!empty($email) && !$emailVal) {
    die("Error: Format email tidak valid!\n");
}

$pdo = Database::getConnection();

// Periksa duplikasi institusi + nim
$stmt = $pdo->prepare("SELECT id FROM users WHERE institution_normalized = ? AND nim_normalized = ?");
$stmt->execute([$institutionNorm, $nimNorm]);
if ($stmt->fetch()) {
    die("Error: Akun dengan kombinasi Institusi dan NIM/ID tersebut sudah terdaftar!\n");
}

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$insertStmt = $pdo->prepare("
    INSERT INTO users (
        name, institution, institution_normalized, nim, nim_normalized,
        program_study, email, password_hash, role, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'admin', 'active')
");

$success = $insertStmt->execute([
    $name,
    $institution,
    $institutionNorm,
    $nim,
    $nimNorm,
    $program_study,
    $emailVal,
    $hash
]);

if ($success) {
    echo "\n[SUKSES] Akun Administrator berhasil dibuat!\n";
    echo "ID Akun         : " . $pdo->lastInsertId() . "\n";
    echo "Nama            : {$name}\n";
    echo "Institusi       : {$institution}\n";
    echo "NIM/ID Login    : {$nim}\n";
    echo "Role            : admin\n";
    echo "Status          : active\n";
    echo "====================================================\n";
} else {
    echo "\n[GAGAL] Terjadi kesalahan saat menyimpan ke database.\n";
}
