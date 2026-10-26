<?php
// app/views/admin/users.php — /admin/users
$href = static fn(string $path): string => htmlspecialchars(BASE_URL . $path, ENT_QUOTES, 'UTF-8');
$cur = (int)$pagination['current_page'];
$tot = (int)$pagination['total_pages'];
$pageUrl = static fn(int $p): string => htmlspecialchars(
    BASE_URL . '/admin/users?' . http_build_query(['q' => $search, 'role' => $role, 'status' => $status, 'page' => $p]),
    ENT_QUOTES, 'UTF-8'
);
$hasFilter = $search !== '' || $role !== '' || $status !== '';
?>
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= $href('/admin') ?>" class="text-decoration-none text-muted">Panel Admin</a></li>
            <li class="breadcrumb-item active text-dark" aria-current="page">Kelola Pengguna</li>
        </ol>
    </nav>

    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1"><i class="bi bi-people-fill me-2 text-primary"></i>Kelola Pengguna</h2>
        <p class="text-muted mb-0">Lihat, cari, dan atur status akun pengguna ClassVault.</p>
    </div>

    <div class="card card-cv p-3 border-0 shadow-sm mb-4">
        <form action="<?= $href('/admin/users') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" name="q"
                           placeholder="Cari nama, institusi, NIM, atau email..." value="<?= Security::e($search) ?>">
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <select class="form-select" name="role" aria-label="Peran">
                    <option value="">Semua Peran</option>
                    <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Mahasiswa</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Administrator</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-3">
                <select class="form-select" name="status" aria-label="Status">
                    <option value="">Semua Status</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="disabled" <?= $status === 'disabled' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100 rounded-3" title="Terapkan"><i class="bi bi-funnel-fill"></i></button>
                <?php if ($hasFilter): ?>
                    <a href="<?= $href('/admin/users') ?>" class="btn btn-light rounded-3 text-danger" title="Reset"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <p class="text-muted small mb-3">
        <strong class="text-dark"><?= number_format((int)$pagination['total_items']) ?></strong> pengguna ditemukan
    </p>

    <?php if (empty($users)): ?>
        <div class="card card-cv p-5 border-0 shadow-sm text-center">
            <div class="empty-state p-0">
                <div class="empty-state-icon"><i class="bi bi-person-x"></i></div>
                <h5 class="fw-bold text-dark">Tidak ada pengguna yang cocok</h5>
                <p class="text-muted mb-0">Coba ubah kata kunci atau filter pencarian.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card card-cv border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Pengguna</th>
                            <th>Institusi / NIM</th>
                            <th>Peran</th>
                            <th>Status</th>
                            <th class="text-center">Dokumen</th>
                            <th>Terdaftar</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold text-dark"><?= Security::e($u['name']) ?></div>
                                <div class="small text-muted"><?= Security::e($u['email'] ?: '—') ?></div>
                            </td>
                            <td>
                                <div class="text-dark"><?= Security::e($u['institution']) ?></div>
                                <div class="small text-muted"><?= Security::e($u['nim']) ?> &bull; <?= Security::e($u['program_study']) ?></div>
                            </td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge bg-dark-subtle text-dark border">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Mahasiswa</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= (int)$u['doc_count'] ?></td>
                            <td class="small text-muted"><?= Security::e(explode(',', View::formatDate($u['created_at']))[0]) ?></td>
                            <td class="text-end pe-4">
                                <a href="<?= $href('/admin/users/' . (int)$u['id']) ?>" class="btn btn-outline-primary btn-sm rounded-3">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($tot > 1): ?>
            <nav class="mt-4" aria-label="Navigasi halaman">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $cur <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $pageUrl(max(1, $cur - 1)) ?>">&lsaquo;</a></li>
                    <?php for ($i = max(1, $cur - 2); $i <= min($tot, $cur + 2); $i++): ?>
                        <li class="page-item <?= $i === $cur ? 'active' : '' ?>"><a class="page-link" href="<?= $pageUrl($i) ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <li class="page-item <?= $cur >= $tot ? 'disabled' : '' ?>"><a class="page-link" href="<?= $pageUrl(min($tot, $cur + 1)) ?>">&rsaquo;</a></li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
