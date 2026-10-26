<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1">Dashboard Administrator</h2>
            <p class="text-muted mb-0">Selamat datang, <?= Security::e($admin['name']) ?> &bull; Panel Pengawasan Sistem ClassVault</p>
        </div>
        <div>
            <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/admin/users" class="btn btn-primary rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2">
                <i class="bi bi-people-fill"></i> Kelola Pengguna
            </a>
        </div>
    </div>

    <!-- Statistik Pengguna -->
    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-people me-2 text-primary"></i>Statistik Pengguna</h5>
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-cv p-3 border-0">
                <div class="text-muted small fw-medium mb-1">Total Pengguna Terdaftar</div>
                <div class="fs-3 fw-bold text-dark"><?= (int)($userStats['total_users'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-cv p-3 border-0">
                <div class="text-muted small fw-medium mb-1">Total Mahasiswa</div>
                <div class="fs-3 fw-bold text-primary"><?= (int)($userStats['total_students'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-cv p-3 border-0">
                <div class="text-muted small fw-medium mb-1">Akun Aktif</div>
                <div class="fs-3 fw-bold text-success"><?= (int)($userStats['total_active'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-cv p-3 border-0">
                <div class="text-muted small fw-medium mb-1">Akun Dinonaktifkan</div>
                <div class="fs-3 fw-bold text-danger"><?= (int)($userStats['total_disabled'] ?? 0) ?></div>
            </div>
        </div>
    </div>

    <!-- Statistik Dokumen Global -->
    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-folder2-open me-2 text-primary"></i>Statistik Dokumen Sistem</h5>
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-cv p-3 border-0">
                <div class="text-muted small fw-medium mb-1">Total Berkas Aktif</div>
                <div class="fs-3 fw-bold text-dark"><?= (int)($docStats['total_docs'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-cv p-3 border-0">
                <div class="text-muted small fw-medium mb-1">Total Kapasitas Penyimpanan</div>
                <div class="fs-3 fw-bold text-primary"><?= View::formatBytes((int)($docStats['total_bytes'] ?? 0)) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-cv p-3 border-0">
                <div class="text-muted small fw-medium mb-1">Dokumen Publik</div>
                <div class="fs-3 fw-bold text-success"><?= (int)($docStats['total_public'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-cv p-3 border-0">
                <div class="text-muted small fw-medium mb-1">Dokumen Privat</div>
                <div class="fs-3 fw-bold text-secondary"><?= (int)($docStats['total_private'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>
