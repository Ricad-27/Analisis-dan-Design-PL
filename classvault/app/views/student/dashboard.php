<div class="container py-4">
    <!-- Header Salam -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1">Halo, <?= Security::e($user['name']) ?>!</h2>
            <p class="text-muted mb-0">
                <i class="bi bi-mortarboard me-1"></i><?= Security::e($user['program_study']) ?> &bull; <?= Security::e($user['institution']) ?> (NIM: <?= Security::e($user['nim']) ?>)
            </p>
        </div>
        <div>
            <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/upload" class="btn btn-primary rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2">
                <i class="bi bi-cloud-arrow-up-fill"></i> Unggah Dokumen Baru
            </a>
        </div>
    </div>

    <!-- Kartu Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-cv p-3 border-0">
                <div class="text-muted small fw-medium mb-1">Total Dokumen Saya</div>
                <div class="fs-3 fw-bold text-dark"><?= (int)($stats['total_files'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-cv p-3 border-0">
                <div class="text-muted small fw-medium mb-1">Penyimpanan Terpakai</div>
                <div class="fs-3 fw-bold text-primary"><?= View::formatBytes((int)($stats['total_bytes'] ?? 0)) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-cv p-3 border-0">
                <div class="text-muted small fw-medium mb-1">Dokumen Privat</div>
                <div class="fs-3 fw-bold text-secondary"><?= (int)($stats['private_count'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-cv p-3 border-0">
                <div class="text-muted small fw-medium mb-1">Dokumen Dibagikan</div>
                <div class="fs-3 fw-bold text-success"><?= (int)($stats['shared_count'] ?? 0) ?></div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Info -->
    <div class="card card-cv p-4 border-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-folder2 me-2 text-primary"></i>Akses Berkas Saya</h5>
            <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/my-documents" class="btn btn-outline-primary btn-sm rounded-3">
                Lihat Semua File <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if ((int)($stats['total_files'] ?? 0) === 0): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-folder-plus"></i>
                </div>
                <h5 class="fw-bold text-dark">Belum ada dokumen yang diunggah</h5>
                <p class="text-muted small mb-3">Mulai simpan tugas kuliah, laporan, atau materi belajar pertama Anda.</p>
                <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/upload" class="btn btn-primary rounded-3 px-3">
                    <i class="bi bi-cloud-arrow-up me-1"></i> Unggah File Pertama
                </a>
            </div>
        <?php else: ?>
            <p class="text-muted small mb-0">
                Silakan buka menu <strong>File Saya</strong> untuk melihat daftar lengkap, mengedit informasi berkas, atau mengunduh dokumen Anda.
            </p>
        <?php endif; ?>
    </div>
</div>
