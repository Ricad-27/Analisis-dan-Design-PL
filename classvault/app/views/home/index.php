<div class="hero-section">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold mb-3">
                    <i class="bi bi-shield-check me-1"></i> Penyimpanan Dokumen Akademik Pribadi
                </div>
                <h1 class="display-4 hero-title fw-bold mb-3">
                    Simpan, Kelola, dan Bagikan Dokumen Kuliah Anda dengan Aman.
                </h1>
                <p class="lead text-muted mb-4">
                    ClassVault dirancang khusus untuk mahasiswa dalam mengorganisasi tugas kuliah, laporan praktikum, proposal, dan catatan kuliah dengan kendali privasi penuh di tangan Anda.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <?php if ($is_logged_in): ?>
                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/dashboard" class="btn btn-primary btn-lg px-4 rounded-3">
                            <i class="bi bi-speedometer2 me-2"></i>Buka Dashboard Saya
                        </a>
                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/my-documents" class="btn btn-outline-primary btn-lg px-4 rounded-3">
                            <i class="bi bi-folder2-open me-2"></i>File Saya
                        </a>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/register" class="btn btn-primary btn-lg px-4 rounded-3">
                            <i class="bi bi-person-plus me-2"></i>Daftar Akun Mahasiswa
                        </a>
                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/explore" class="btn btn-outline-primary btn-lg px-4 rounded-3">
                            <i class="bi bi-compass me-2"></i>Jelajahi Dokumen Publik
                        </a>
                    <?php endif; ?>
                </div>
                <?php if (!$is_logged_in): ?>
                    <p class="small text-muted mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Ingin melihat-lihat dulu? Dokumen publik dapat dijelajahi dan diunduh <strong>tanpa login</strong>.
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-lg-5">
                <div class="card card-cv p-4 shadow-sm border-0">
                    <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-lock me-2 text-primary"></i>Kendali Privasi 3 Tingkat</h5>
                    <div class="list-group list-group-flush border-0">
                        <div class="list-group-item px-0 border-bottom py-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge badge-visibility-private px-2 py-1"><i class="bi bi-lock-fill me-1"></i>Private</span>
                                <span class="fw-semibold text-dark">Hanya Anda</span>
                            </div>
                            <div class="small text-muted">Hanya Anda yang dapat melihat dan mengunduh file ini. Pengaturan bawaan untuk seluruh file yang diunggah.</div>
                        </div>
                        <div class="list-group-item px-0 border-bottom py-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge badge-visibility-authenticated px-2 py-1"><i class="bi bi-people-fill me-1"></i>Pengguna Login</span>
                                <span class="fw-semibold text-dark">Mahasiswa Terdaftar</span>
                            </div>
                            <div class="small text-muted">Dapat diakses oleh seluruh mahasiswa yang telah masuk (login) ke sistem ClassVault.</div>
                        </div>
                        <div class="list-group-item px-0 py-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge badge-visibility-public px-2 py-1"><i class="bi bi-globe me-1"></i>Publik</span>
                                <span class="fw-semibold text-dark">Akses Terbuka</span>
                            </div>
                            <div class="small text-muted">Dapat dilihat dan diunduh oleh siapa saja tanpa perlu masuk ke akun terlebih dahulu.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fitur Utama -->
<div class="container py-5">
    <div class="row text-center mb-5">
        <div class="col-lg-8 mx-auto">
            <h2 class="fw-bold text-dark">Mengapa Menggunakan ClassVault?</h2>
            <p class="text-muted">Fokus pada kenyamanan, kecepatan, dan keamanan berkas akademik Anda.</p>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card card-cv h-100 p-4 border-0">
                <div class="brand-icon mb-3 fs-5" style="width: 44px; height: 44px; border-radius: 10px;">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h5 class="fw-bold text-dark">Keamanan Tingkat Tinggi</h5>
                <p class="text-muted small mb-0">
                    Semua berkas disimpan dengan nama acak terenkripsi di luar direktori web publik, memblokir eksekusi file asing secara menyeluruh.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-cv h-100 p-4 border-0">
                <div class="brand-icon mb-3 fs-5" style="width: 44px; height: 44px; border-radius: 10px;">
                    <i class="bi bi-tags-fill"></i>
                </div>
                <h5 class="fw-bold text-dark">Kategori Akademik Rapi</h5>
                <p class="text-muted small mb-0">
                    Kelompokkan file Anda ke dalam Tugas Kuliah, Laporan Praktikum, Catatan, Materi Belajar, atau Proposal dengan pencarian cepat.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-cv h-100 p-4 border-0">
                <div class="brand-icon mb-3 fs-5" style="width: 44px; height: 44px; border-radius: 10px;">
                    <i class="bi bi-download"></i>
                </div>
                <h5 class="fw-bold text-dark">Unduhan Terproteksi</h5>
                <p class="text-muted small mb-0">
                    Tiap permintaan unduhan diverifikasi hak aksesnya di server secara ketat. Tidak ada tautan langsung terbuka ke folder server.
                </p>
            </div>
        </div>
    </div>
</div>
