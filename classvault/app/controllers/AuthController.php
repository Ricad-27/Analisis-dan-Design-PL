<?php
/**
 * ClassVault - AuthController
 * Menangani alur registrasi mahasiswa, login dengan brute-force protection, dan logout.
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Security.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/View.php';

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Tampilkan formulir registrasi mahasiswa
     */
    public function showRegister(): void {
        AuthMiddleware::requireGuest();

        View::render('auth/register', [
            'page_title' => 'Pendaftaran Mahasiswa',
            'errors'     => [],
            'old'        => []
        ]);
    }

    /**
     * Proses pendaftaran mahasiswa
     */
    public function register(): void {
        AuthMiddleware::requireGuest();

        // Validasi CSRF
        if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            View::render('auth/register', [
                'page_title' => 'Pendaftaran Mahasiswa',
                'errors'     => ['Sesi form telah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.'],
                'old'        => $_POST
            ]);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $institution = trim($_POST['institution'] ?? '');
        $nim = trim($_POST['nim'] ?? '');
        $program_study = trim($_POST['program_study'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        $errors = [];

        // Validasi Sisi Server
        if (empty($name)) {
            $errors[] = 'Nama tampilan wajib diisi.';
        } elseif (mb_strlen($name) < 2) {
            $errors[] = 'Nama tampilan minimal 2 karakter.';
        }

        if (empty($institution)) {
            $errors[] = 'Institusi / Universitas wajib diisi.';
        }

        if (empty($nim)) {
            $errors[] = 'NIM (Nomor Induk Mahasiswa) wajib diisi.';
        }

        if (empty($program_study)) {
            $errors[] = 'Program studi wajib diisi.';
        }

        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Format alamat email tidak valid.';
            } elseif ($this->userModel->isEmailTaken($email)) {
                $errors[] = 'Alamat email ini sudah terdaftar pada akun lain.';
            }
        }

        if (empty($password)) {
            $errors[] = 'Kata sandi wajib diisi.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Kata sandi minimal harus 8 karakter.';
        }

        if ($password !== $password_confirm) {
            $errors[] = 'Konfirmasi kata sandi tidak cocok.';
        }

        // Cek Keunikan Institusi + NIM (dengan normalisasi spasi & huruf)
        if (empty($errors)) {
            if ($this->userModel->isInstitutionNimTaken($institution, $nim)) {
                $errors[] = 'Kombinasi Institusi dan NIM ini sudah terdaftar. Silakan masuk menggunakan akun Anda.';
            }
        }

        // Jika ada kesalahan validasi, kembalikan ke form
        if (!empty($errors)) {
            View::render('auth/register', [
                'page_title' => 'Pendaftaran Mahasiswa',
                'errors'     => $errors,
                'old'        => [
                    'name'          => $name,
                    'institution'   => $institution,
                    'nim'           => $nim,
                    'program_study' => $program_study,
                    'email'         => $email
                ]
            ]);
            return;
        }

        // Simpan data pendaftaran
        try {
            $this->userModel->registerStudent([
                'name'          => $name,
                'institution'   => $institution,
                'nim'           => $nim,
                'program_study' => $program_study,
                'email'         => $email,
                'password'      => $password
            ]);

            View::redirect('/login', 'success', 'Pendaftaran berhasil! Silakan masuk dengan akun yang baru Anda buat.');
        } catch (Exception $e) {
            error_log('Registration Error: ' . $e->getMessage());
            View::render('auth/register', [
                'page_title' => 'Pendaftaran Mahasiswa',
                'errors'     => ['Terjadi kesalahan saat memproses pendaftaran. Silakan coba beberapa saat lagi.'],
                'old'        => $_POST
            ]);
        }
    }

    /**
     * Tampilkan formulir login
     */
    public function showLogin(): void {
        AuthMiddleware::requireGuest();

        View::render('auth/login', [
            'page_title' => 'Masuk ke ClassVault',
            'errors'     => [],
            'old'        => []
        ]);
    }

    /**
     * Proses autentikasi login pengguna
     */
    public function login(): void {
        AuthMiddleware::requireGuest();

        // Validasi CSRF
        if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            View::render('auth/login', [
                'page_title' => 'Masuk ke ClassVault',
                'errors'     => ['Sesi keamanan form telah berakhir. Silakan ulangi pengisian.'],
                'old'        => $_POST
            ]);
            return;
        }

        $institution = trim($_POST['institution'] ?? '');
        $nim = trim($_POST['nim'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $identifier = Security::normalize($institution) . '|' . Security::normalize($nim);

        $errors = [];

        if (empty($institution) || empty($nim) || empty($password)) {
            $errors[] = 'Institusi, NIM/ID Pengguna, dan kata sandi wajib diisi.';
            View::render('auth/login', [
                'page_title' => 'Masuk ke ClassVault',
                'errors'     => $errors,
                'old'        => ['institution' => $institution, 'nim' => $nim]
            ]);
            return;
        }

        // Perlindungan Brute-Force: Cek apakah terkunci (5 kali percobaan dalam 15 menit)
        if ($this->userModel->isRateLimited($ip, $identifier, 5, 15)) {
            View::render('auth/login', [
                'page_title' => 'Masuk ke ClassVault',
                'errors'     => ['Terlalu banyak percobaan masuk yang gagal. Untuk keamanan akun Anda, silakan coba kembali setelah 15 menit.'],
                'old'        => ['institution' => $institution, 'nim' => $nim]
            ]);
            return;
        }

        // Cari Pengguna
        $user = $this->userModel->findForAuth($institution, $nim);

        // Jika tidak ditemukan dan input NIM berbentuk email, coba cek via email
        if (!$user && filter_var($nim, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userModel->findByEmail($nim);
        }

        // Verifikasi Akun & Password
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->userModel->recordFailedLogin($ip, $identifier);
            View::render('auth/login', [
                'page_title' => 'Masuk ke ClassVault',
                'errors'     => ['Institusi, NIM/ID, atau kata sandi tidak sesuai.'],
                'old'        => ['institution' => $institution, 'nim' => $nim]
            ]);
            return;
        }

        // Periksa Status Akun
        if ($user['status'] === 'disabled') {
            View::render('auth/login', [
                'page_title' => 'Masuk ke ClassVault',
                'errors'     => ['Akun Anda telah dinonaktifkan oleh administrator. Silakan hubungi pihak kampus atau admin.'],
                'old'        => ['institution' => $institution, 'nim' => $nim]
            ]);
            return;
        }

        // Sukses Login: Bersihkan catatan percobaan gagal
        $this->userModel->clearLoginAttempts($ip, $identifier);

        // Regenerasi Session ID untuk mencegah session fixation
        Session::regenerate();

        // Simpan sesi autentikasi
        Session::set('user_id', (int)$user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user', [
            'id'            => (int)$user['id'],
            'name'          => $user['name'],
            'institution'   => $user['institution'],
            'nim'           => $user['nim'],
            'program_study' => $user['program_study'],
            'email'         => $user['email'],
            'role'          => $user['role']
        ]);

        if ($user['role'] === 'admin') {
            View::redirect('/admin', 'success', 'Selamat datang kembali, Administrator ' . Security::e($user['name']) . '!');
        } else {
            View::redirect('/dashboard', 'success', 'Selamat datang di ClassVault, ' . Security::e($user['name']) . '!');
        }
    }

    /**
     * Proses keluar (logout) aman
     */
    public function logout(): void {
        // Validasi CSRF jika dikirim melalui form POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
                View::redirect('/', 'danger', 'Sesi tidak valid.');
            }
        }

        Session::destroy();
        View::redirect('/login', 'info', 'Anda telah berhasil keluar dari akun.');
    }
}
