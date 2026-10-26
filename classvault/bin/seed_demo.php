<?php
/**
 * ClassVault - Seeder Data Demo
 * Membuat beberapa dokumen contoh agar halaman "Jelajahi Dokumen" tidak kosong
 * saat diperiksa dosen / pengunjung tanpa login.
 *
 * Penggunaan:  php bin/seed_demo.php
 * Aman dijalankan berulang kali (tidak membuat data ganda).
 * Akun demo TIDAK dapat dipakai login (password acak, tidak ditampilkan).
 */

if (php_sapi_name() !== 'cli') {
    die("Perintah ini hanya dapat dijalankan melalui Terminal CLI.\n");
}

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/Security.php';

$db = Database::getConnection();
$uploadDir = STORAGE_PATH . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// --- Akun pemilik dokumen demo (tanpa kemampuan login) ---
$institution = 'Universitas Demo ClassVault';
$nim         = 'DEMO001';

$stmt = $db->prepare("SELECT id FROM users WHERE institution_normalized = ? AND nim_normalized = ?");
$stmt->execute([Security::normalize($institution), Security::normalize($nim)]);
$ownerId = $stmt->fetchColumn();

if (!$ownerId) {
    $stmt = $db->prepare("
        INSERT INTO users (name, institution, institution_normalized, nim, nim_normalized,
                           program_study, email, password_hash, role, status)
        VALUES (?, ?, ?, ?, ?, ?, NULL, ?, 'student', 'active')
    ");
    $stmt->execute([
        'Mahasiswa Demo', $institution, Security::normalize($institution),
        $nim, Security::normalize($nim), 'Teknik Informatika',
        password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT),
    ]);
    $ownerId = (int)$db->lastInsertId();
    echo "[+] Akun pemilik demo dibuat (id {$ownerId}).\n";
}

$has = $db->prepare("SELECT COUNT(*) FROM documents WHERE owner_id = ?");
$has->execute([$ownerId]);
if ((int)$has->fetchColumn() > 0) {
    echo "[=] Data demo sudah ada, tidak ada yang ditambahkan.\n";
    exit(0);
}

$cat = [];
foreach ($db->query("SELECT id, name FROM categories") as $row) {
    $cat[$row['name']] = (int)$row['id'];
}

// PDF minimal yang valid
function demo_pdf(string $text): string {
    $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    $stream = "BT /F1 18 Tf 72 720 Td ({$text}) Tj ET";
    $objs = [
        "<< /Type /Catalog /Pages 2 0 R >>",
        "<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
        "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>",
        "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}\nendstream",
        "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
    ];
    $pdf = "%PDF-1.4\n"; $offsets = [];
    foreach ($objs as $i => $o) {
        $offsets[] = strlen($pdf);
        $pdf .= ($i + 1) . " 0 obj\n{$o}\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objs) + 1) . "\n0000000000 65535 f \n";
    foreach ($offsets as $off) { $pdf .= sprintf("%010d 00000 n \n", $off); }
    $pdf .= "trailer\n<< /Size " . (count($objs) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    return $pdf;
}

$demo = [
    ['Ringkasan Materi Basis Data', 'Catatan', 'public', 'ringkasan-basis-data.txt', 'text/plain',
     "Ringkasan Materi Basis Data\n\n1. Normalisasi: 1NF, 2NF, 3NF, BCNF.\n2. Relasi antar tabel: one-to-one, one-to-many, many-to-many.\n3. Perintah dasar SQL: SELECT, INSERT, UPDATE, DELETE.\n\n(Dokumen contoh untuk demo ClassVault.)\n",
     'Catatan singkat normalisasi dan SQL dasar untuk persiapan UTS.'],
    ['Laporan Praktikum Jaringan Komputer Modul 3', 'Laporan Praktikum', 'public', 'laporan-jarkom-modul3.pdf', 'application/pdf',
     demo_pdf('Laporan Praktikum Jaringan Komputer - Modul 3 (contoh)'),
     'Konfigurasi subnetting dan routing statis menggunakan Cisco Packet Tracer.'],
    ['Proposal Sistem Informasi Perpustakaan', 'Proposal', 'public', 'proposal-sip.pdf', 'application/pdf',
     demo_pdf('Proposal Sistem Informasi Perpustakaan (contoh)'),
     'Proposal pengembangan sistem informasi perpustakaan berbasis web.'],
    ['Tugas Algoritma: Analisis Kompleksitas', 'Tugas Kuliah', 'public', 'tugas-algoritma.txt', 'text/plain',
     "Tugas Algoritma dan Struktur Data\n\nAnalisis kompleksitas waktu: Bubble Sort O(n^2), Merge Sort O(n log n), Binary Search O(log n).\n\n(Dokumen contoh untuk demo ClassVault.)\n",
     'Perbandingan kompleksitas algoritma pengurutan dan pencarian.'],
    ['Materi Pemrograman Web: Dasar PHP', 'Materi Belajar', 'authenticated', 'materi-php.txt', 'text/plain',
     "Dasar PHP: variabel, array, fungsi, PDO, session.\n\n(Contoh dokumen khusus mahasiswa terdaftar: tidak tampil untuk tamu.)\n",
     'Hanya terlihat oleh pengguna yang sudah login.'],
    ['Catatan Pribadi (Private)', 'Catatan', 'private', 'catatan-pribadi.txt', 'text/plain',
     "Catatan pribadi pemilik. Tidak dapat diakses siapa pun selain pemilik.\n",
     'Contoh dokumen private: tidak tampil di mana pun untuk tamu.'],
];

$ins = $db->prepare("
    INSERT INTO documents (owner_id, category_id, title, description, original_filename,
                           storage_filename, mime_type, file_size, visibility, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$n = 0;
foreach ($demo as $i => [$title, $category, $vis, $orig, $mime, $content, $desc]) {
    $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
    $storage = Security::generateStorageName($ext);
    file_put_contents($uploadDir . DIRECTORY_SEPARATOR . $storage, $content);
    $when = date('Y-m-d H:i:s', time() - ($i * 86400));
    $ins->execute([$ownerId, $cat[$category] ?? reset($cat), $title, $desc, $orig,
                   $storage, $mime, strlen($content), $vis, $when, $when]);
    $n++;
}

echo "[+] {$n} dokumen demo ditambahkan (4 publik, 1 khusus mahasiswa, 1 private).\n";
echo "    Buka /explore tanpa login untuk melihat hasilnya.\n";
