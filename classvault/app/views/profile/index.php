<?php
// app/views/profile/index.php
$user   ??= [];
$errors ??= [];
$old    ??= [];
$href = static fn(string $path): string => htmlspecialchars(BASE_URL . $path, ENT_QUOTES, 'UTF-8');
$val  = static fn(string $key): string => Security::e((string)($old[$key] ?? $user[$key] ?? ''));
?>
<div class="container py-4">
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Profil Saya</h2>
        <p class="text-muted mb-0">Kelola informasi akun Anda.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card card-cv p-4 border-0 shadow-sm">
                <div class="text-center mb-3">
                    <span class="brand-icon mx-auto mb-2" style="width:64px;height:64px;border-radius:50%;font-size:1.6rem;">
                        <?= Security::e(mb_strtoupper(mb_substr($user['name'] ?? 'U', 0, 1))) ?>
                    </span>
                    <h5 class="fw-bold text-dark mb-0"><?= Security::e($user['name'] ?? '') ?></h5>
                    <div class="small text-muted"><?= Security::e($user['institution'] ?? '') ?></div>
                </div>
                <div class="list-group list-group-flush small">
                    <div class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">NIM</span><span class="fw-medium text-dark"><?= Security::e($user['nim'] ?? '-') ?></span></div>
                    <div class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Peran</span><span class="fw-medium text-dark"><?= ($user['role'] ?? '') === 'admin' ? 'Administrator' : 'Mahasiswa' ?></span></div>
                    <div class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Status</span><span class="fw-medium text-dark"><?= ($user['status'] ?? '') === 'active' ? 'Aktif' : 'Nonaktif' ?></span></div>
                    <div class="list-group-item px-0 d-flex justify-content-between"><span class="text-muted">Bergabung</span><span class="fw-medium text-dark text-end"><?= Security::e(View::formatDate($user['created_at'] ?? '')) ?></span></div>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <a href="<?= $href('/my-documents') ?>" class="btn btn-outline-primary btn-sm rounded-3"><i class="bi bi-folder2 me-1"></i>File Saya</a>
                    <a href="<?= $href('/change-password') ?>" class="btn btn-outline-secondary btn-sm rounded-3"><i class="bi bi-key me-1"></i>Ubah Password</a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-cv p-4 border-0 shadow-sm">
                <h5 class="fw-bold text-dark mb-1">Edit Profil</h5>
                <p class="text-muted small mb-4">Nama, program studi, dan email dapat diubah. Institusi dan NIM bersifat permanen.</p>
                <form method="POST" action="<?= $href('/profile') ?>" novalidate>
                    <?= $csrf_field ?>
                    <div class="mb-3">
                        <label for="profile-name" class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" id="profile-name" name="name" maxlength="100" required
                               class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= $val('name') ?>">
                        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= Security::e($errors['name']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="profile-prodi" class="form-label fw-medium">Program Studi <span class="text-danger">*</span></label>
                        <input type="text" id="profile-prodi" name="program_study" maxlength="100" required
                               class="form-control <?= isset($errors['program_study']) ? 'is-invalid' : '' ?>" value="<?= $val('program_study') ?>">
                        <?php if (isset($errors['program_study'])): ?><div class="invalid-feedback"><?= Security::e($errors['program_study']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="profile-email" class="form-label fw-medium">Email <span class="text-muted fw-normal">(opsional)</span></label>
                        <input type="email" id="profile-email" name="email" maxlength="150"
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= $val('email') ?>">
                        <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= Security::e($errors['email']) ?></div><?php endif; ?>
                    </div>
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Institusi</label>
                            <input type="text" class="form-control" value="<?= Security::e($user['institution'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">NIM</label>
                            <input type="text" class="form-control" value="<?= Security::e($user['nim'] ?? '') ?>" disabled>
                        </div>
                    </div>
                    <p class="small text-muted">Institusi dan NIM tidak dapat diubah setelah pendaftaran.</p>
                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn btn-primary rounded-3 px-4">Simpan Perubahan</button>
                        <a href="<?= $href('/dashboard') ?>" class="btn btn-light rounded-3 px-3">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
