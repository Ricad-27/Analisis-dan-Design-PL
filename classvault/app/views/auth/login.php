<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-cv p-4 p-md-5 border-0 shadow-sm">
                <div class="text-center mb-4">
                    <div class="brand-icon mx-auto mb-2" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1.5rem;">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">Masuk ke ClassVault</h3>
                    <p class="text-muted small">Akses dokumen akademik dan ruang penyimpanan Anda.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger rounded-3 py-2 px-3 small border-0 shadow-sm mb-4">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= Security::e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/login" method="POST" novalidate>
                    <?= $csrf_field ?>

                    <!-- Institusi -->
                    <div class="mb-3">
                        <label for="institution" class="form-label small fw-semibold text-dark">Institusi / Universitas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="institution" name="institution" 
                               value="<?= Security::e($old['institution'] ?? '') ?>" 
                               placeholder="Contoh: Universitas Indonesia" required autofocus>
                    </div>

                    <!-- NIM / ID -->
                    <div class="mb-3">
                        <label for="nim" class="form-label small fw-semibold text-dark">NIM atau ID Pengguna <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nim" name="nim" 
                               value="<?= Security::e($old['nim'] ?? '') ?>" 
                               placeholder="Masukkan NIM Anda" required>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="form-label small fw-semibold text-dark">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Masukkan kata sandi" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-semibold mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk Sekarang
                    </button>

                    <div class="text-center small text-muted">
                        Belum memiliki akun mahasiswa? 
                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/register" class="text-primary fw-semibold text-decoration-none">
                            Daftar di sini
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
