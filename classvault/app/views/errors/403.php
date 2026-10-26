<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <div class="display-1 fw-bold text-danger mb-3">403</div>
                <h2 class="fw-bold mb-3 text-dark">Akses Ditolak</h2>
                <p class="text-muted mb-4">
                    <?= Security::e($message ?? 'Maaf, Anda tidak memiliki izin untuk mengakses halaman atau dokumen ini.') ?>
                </p>
                <div>
                    <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/" class="btn btn-primary px-4 py-2 rounded-3">
                        <i class="bi bi-house-door me-2"></i>Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
