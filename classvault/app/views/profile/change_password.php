<?php
// app/views/profile/change_password.php
$user   ??= [];
$errors ??= [];
$href = static fn(string $path): string => htmlspecialchars(BASE_URL . $path, ENT_QUOTES, 'UTF-8');
$field = static function (string $id, string $name, string $label, string $hint = '') use ($errors): void {
    ?>
    <div class="mb-3">
        <label for="<?= $id ?>" class="form-label fw-medium"><?= Security::e($label) ?> <span class="text-danger">*</span></label>
        <input type="password" id="<?= $id ?>" name="<?= $name ?>" required autocomplete="<?= $name === 'current_password' ? 'current-password' : 'new-password' ?>"
               class="form-control <?= isset($errors[$name]) ? 'is-invalid' : '' ?>">
        <?php if (isset($errors[$name])): ?>
            <div class="invalid-feedback"><?= Security::e($errors[$name]) ?></div>
        <?php elseif ($hint !== ''): ?>
            <div class="form-text"><?= Security::e($hint) ?></div>
        <?php endif; ?>
    </div>
    <?php
};
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="<?= $href('/profile') ?>" class="text-decoration-none text-muted">Profil</a></li>
                    <li class="breadcrumb-item active text-dark" aria-current="page">Ubah Password</li>
                </ol>
            </nav>
            <div class="card card-cv p-4 border-0 shadow-sm">
                <h4 class="fw-bold text-dark mb-1"><i class="bi bi-key me-2 text-primary"></i>Ubah Password</h4>
                <p class="text-muted small mb-4">Setelah password diubah, Anda perlu masuk kembali.</p>
                <form method="POST" action="<?= $href('/change-password') ?>" novalidate>
                    <?= $csrf_field ?>
                    <?php $field('current-password', 'current_password', 'Password Saat Ini'); ?>
                    <?php $field('new-password', 'new_password', 'Password Baru', 'Minimal 8 karakter.'); ?>
                    <?php $field('confirm-password', 'confirm_password', 'Konfirmasi Password Baru'); ?>
                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn btn-primary rounded-3 px-4">Ubah Password</button>
                        <a href="<?= $href('/profile') ?>" class="btn btn-light rounded-3 px-3">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
