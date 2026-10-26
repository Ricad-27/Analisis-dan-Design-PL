<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card card-cv p-4 p-md-5 border-0 shadow-sm">
                <div class="text-center mb-4">
                    <div class="brand-icon mx-auto mb-2" style="width: 48px; height: 48px; border-radius: 12px; font-size: 1.5rem;">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">Daftar Akun Mahasiswa</h3>
                    <p class="text-muted small">Buat ruang penyimpanan berkas akademik pribadi Anda.</p>
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

                <form action="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/register" method="POST" novalidate>
                    <?= $csrf_field ?>

                    <!-- Nama Lengkap -->
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold text-dark">Nama Lengkap / Tampilan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= Security::e($old['name'] ?? '') ?>" 
                               placeholder="Contoh: Budi Santoso" required autofocus>
                    </div>

                    <!-- Institusi & NIM -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-7">
                            <label for="institution" class="form-label small fw-semibold text-dark">Institusi / Universitas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="institution" name="institution" 
                                   value="<?= Security::e($old['institution'] ?? '') ?>" 
                                   placeholder="Contoh: Universitas Indonesia" required>
                        </div>
                        <div class="col-sm-5">
                            <label for="nim" class="form-label small fw-semibold text-dark">NIM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nim" name="nim" 
                                   value="<?= Security::e($old['nim'] ?? '') ?>" 
                                   placeholder="Contoh: 21060123" required>
                        </div>
                    </div>

                    <!-- Program Studi -->
                    <div class="mb-3">
                        <label for="program_study" class="form-label small fw-semibold text-dark">Program Studi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="program_study" name="program_study" 
                               value="<?= Security::e($old['program_study'] ?? '') ?>" 
                               placeholder="Contoh: Sistem Informasi" required>
                    </div>

                    <!-- Email (Opsional) -->
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold text-dark d-flex justify-content-between">
                            <span>Alamat Email</span>
                            <span class="text-muted fw-normal">(Opsional)</span>
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= Security::e($old['email'] ?? '') ?>" 
                               placeholder="nama@kampus.ac.id">
                        <div class="form-text small">Dapat digunakan jika akun memerlukan pemulihan atau verifikasi.</div>
                    </div>

                    <!-- Password & Konfirmasi -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label for="password" class="form-label small fw-semibold text-dark">Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Minimal 8 karakter" minlength="8" required>
                        </div>
                        <div class="col-sm-6">
                            <label for="password_confirm" class="form-label small fw-semibold text-dark">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                   placeholder="Ketik ulang sandi" minlength="8" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-semibold mb-3">
                        <i class="bi bi-person-check-fill me-2"></i>Daftar Sekarang
                    </button>

                    <div class="text-center small text-muted">
                        Sudah memiliki akun? 
                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/login" class="text-primary fw-semibold text-decoration-none">
                            Masuk di sini
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
