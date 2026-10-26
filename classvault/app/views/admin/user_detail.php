<?php
// app/views/admin/user_detail.php — /admin/users/{id}
$href = static fn(string $path): string => htmlspecialchars(BASE_URL . $path, ENT_QUOTES, 'UTF-8');
$isSelf    = (int)$target['id'] === (int)$admin['id'];
$isAdmin   = $target['role'] === 'admin';
$isActive  = $target['status'] === 'active';
$canManage = !$isSelf && !$isAdmin;
?>
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= $href('/admin') ?>" class="text-decoration-none text-muted">Panel Admin</a></li>
            <li class="breadcrumb-item"><a href="<?= $href('/admin/users') ?>" class="text-decoration-none text-muted">Kelola Pengguna</a></li>
            <li class="breadcrumb-item active text-dark" aria-current="page"><?= Security::e($target['name']) ?></li>
        </ol>
    </nav>

    <?php if (!empty($tempPassword)): ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-3" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-key-fill me-1"></i>Password sementara (hanya tampil sekali)</div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <code class="fs-5 px-3 py-1 bg-white rounded-3 border user-select-all"><?= Security::e($tempPassword) ?></code>
                <span class="small">Berikan kepada pengguna dan minta segera menggantinya lewat menu Ubah Password. Halaman ini tidak akan menampilkannya lagi.</span>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card card-cv p-4 border-0 shadow-sm">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <span class="brand-icon flex-shrink-0" style="width:60px;height:60px;border-radius:50%;font-size:1.5rem;">
                        <?= Security::e(mb_strtoupper(mb_substr($target['name'], 0, 1))) ?>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-1"><?= Security::e($target['name']) ?></h4>
                        <span class="badge <?= $isAdmin ? 'bg-dark-subtle text-dark border' : 'bg-primary-subtle text-primary border border-primary-subtle' ?>">
                            <?= $isAdmin ? 'Administrator' : 'Mahasiswa' ?>
                        </span>
                        <?php if ($isActive): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Nonaktif</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    <?php
                    $rows = [
                        'Institusi'     => $target['institution'],
                        'NIM'           => $target['nim'],
                        'Program Studi' => $target['program_study'],
                        'Email'         => $target['email'] ?: '—',
                        'Terdaftar'     => View::formatDate($target['created_at']),
                        'Diperbarui'    => View::formatDate($target['updated_at']),
                    ];
                    foreach ($rows as $label => $value): ?>
                        <div class="list-group-item px-0 d-flex justify-content-between gap-3">
                            <span class="text-muted"><?= Security::e($label) ?></span>
                            <span class="fw-medium text-dark text-end"><?= Security::e($value) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-cv p-4 border-0 shadow-sm mb-4">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-folder2-open me-2 text-primary"></i>Ringkasan Dokumen</h6>
                <div class="row g-2 text-center">
                    <div class="col-6"><div class="p-2 bg-light rounded-3"><div class="fs-4 fw-bold text-dark"><?= (int)($stats['total_docs'] ?? 0) ?></div><div class="small text-muted">Total</div></div></div>
                    <div class="col-6"><div class="p-2 bg-light rounded-3"><div class="fs-4 fw-bold text-primary"><?= View::formatBytes((int)($stats['total_bytes'] ?? 0)) ?></div><div class="small text-muted">Penyimpanan</div></div></div>
                    <div class="col-4"><div class="p-2 bg-light rounded-3"><div class="fw-bold text-success"><?= (int)($stats['total_public'] ?? 0) ?></div><div class="small text-muted">Publik</div></div></div>
                    <div class="col-4"><div class="p-2 bg-light rounded-3"><div class="fw-bold text-info"><?= (int)($stats['total_authenticated'] ?? 0) ?></div><div class="small text-muted">Mahasiswa</div></div></div>
                    <div class="col-4"><div class="p-2 bg-light rounded-3"><div class="fw-bold text-secondary"><?= (int)($stats['total_private'] ?? 0) ?></div><div class="small text-muted">Private</div></div></div>
                </div>
                <p class="small text-muted mt-3 mb-0"><i class="bi bi-shield-lock me-1"></i>Demi privasi, admin hanya melihat jumlah dokumen, bukan isi atau judul dokumen private.</p>
            </div>

            <div class="card card-cv p-4 border-0 shadow-sm">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-gear me-2 text-primary"></i>Tindakan Akun</h6>
                <?php if (!$canManage): ?>
                    <p class="small text-muted mb-0">
                        <?= $isSelf ? 'Ini akun Anda sendiri; tindakan tidak tersedia.' : 'Akun administrator tidak dapat dikelola melalui panel ini.' ?>
                    </p>
                <?php else: ?>
                    <form method="POST" action="<?= $href('/admin/users/' . (int)$target['id'] . '/status') ?>" class="mb-3"
                          onsubmit="return confirm('<?= $isActive ? 'Nonaktifkan akun ini? Pengguna tidak akan dapat masuk.' : 'Aktifkan kembali akun ini?' ?>');">
                        <?= $csrf_field ?>
                        <button type="submit" class="btn <?= $isActive ? 'btn-outline-danger' : 'btn-success' ?> w-100 rounded-3">
                            <i class="bi <?= $isActive ? 'bi-person-slash' : 'bi-person-check' ?> me-1"></i>
                            <?= $isActive ? 'Nonaktifkan Akun' : 'Aktifkan Kembali Akun' ?>
                        </button>
                    </form>
                    <form method="POST" action="<?= $href('/admin/users/' . (int)$target['id'] . '/reset-password') ?>"
                          onsubmit="return confirm('Reset password pengguna ini? Password lama tidak berlaku lagi.');">
                        <?= $csrf_field ?>
                        <button type="submit" class="btn btn-outline-secondary w-100 rounded-3">
                            <i class="bi bi-key me-1"></i>Reset Password
                        </button>
                    </form>
                    <p class="small text-muted mt-3 mb-0">Reset membuat password sementara acak yang tampil sekali. Password lama pengguna tidak pernah ditampilkan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
