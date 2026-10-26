<?php
/**
 * Partial: daftar dokumen (filter + kartu + pagination)
 * Dipakai oleh explore/index.php (/explore) dan explore/shared.php (/shared-documents).
 *
 * Variabel yang dibutuhkan: $route, $documents, $pagination, $categories,
 * $search, $catId, $sort, $emptyTitle, $emptyText
 */
$href = static fn(string $path): string => htmlspecialchars(BASE_URL . $path, ENT_QUOTES, 'UTF-8');

$queryBase = ['q' => $search, 'category' => $catId, 'sort' => $sort];
$hasFilter = $search !== '' || $catId > 0 || $sort !== 'date_desc';
$cur = (int)$pagination['current_page'];
$tot = (int)$pagination['total_pages'];
$pageUrl = static fn(int $p): string => htmlspecialchars(
    BASE_URL . '/' . $route . '?' . http_build_query(array_merge($queryBase, ['page' => $p])),
    ENT_QUOTES, 'UTF-8'
);
?>
<!-- Filter & Pencarian -->
<div class="card card-cv p-3 border-0 shadow-sm mb-4">
    <form action="<?= $href('/' . $route) ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-start-0 ps-0" name="q"
                       placeholder="Cari judul atau deskripsi..." value="<?= Security::e($search) ?>">
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <select class="form-select" name="category" aria-label="Kategori">
                <option value="0">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= $catId === (int)$cat['id'] ? 'selected' : '' ?>>
                        <?= Security::e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6 col-md-3">
            <select class="form-select" name="sort" aria-label="Urutkan">
                <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Terbaru</option>
                <option value="date_asc"  <?= $sort === 'date_asc'  ? 'selected' : '' ?>>Terlama</option>
                <option value="name_asc"  <?= $sort === 'name_asc'  ? 'selected' : '' ?>>Nama (A-Z)</option>
                <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Nama (Z-A)</option>
                <option value="size_desc" <?= $sort === 'size_desc' ? 'selected' : '' ?>>Ukuran Terbesar</option>
                <option value="size_asc"  <?= $sort === 'size_asc'  ? 'selected' : '' ?>>Ukuran Terkecil</option>
            </select>
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100 rounded-3" title="Terapkan"><i class="bi bi-funnel-fill"></i></button>
            <?php if ($hasFilter): ?>
                <a href="<?= $href('/' . $route) ?>" class="btn btn-light rounded-3 text-danger" title="Reset"><i class="bi bi-x-circle"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<p class="text-muted small mb-3">
    <?php if ((int)$pagination['total_items'] > 0): ?>
        Ditemukan <strong class="text-dark"><?= number_format((int)$pagination['total_items']) ?></strong> dokumen
        <?php if ($search !== ''): ?>untuk &ldquo;<em><?= Security::e($search) ?></em>&rdquo;<?php endif; ?>
    <?php else: ?>
        Tidak ada dokumen yang cocok.
    <?php endif; ?>
</p>

<?php if (empty($documents)): ?>
    <div class="card card-cv p-5 border-0 shadow-sm text-center">
        <div class="empty-state p-0">
            <div class="empty-state-icon"><i class="bi bi-folder2-open"></i></div>
            <h5 class="fw-bold text-dark"><?= Security::e($emptyTitle) ?></h5>
            <p class="text-muted mb-0">
                <?= ($search !== '' || $catId > 0) ? 'Coba ubah kata kunci atau kategori pencarian.' : Security::e($emptyText) ?>
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($documents as $d): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card card-cv h-100 p-4 border-0 shadow-sm d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="fs-2 lh-1"><?= View::fileIcon(pathinfo($d['original_filename'], PATHINFO_EXTENSION)) ?></span>
                        <?php if ($d['visibility'] === 'public'): ?>
                            <span class="badge badge-visibility-public px-2 py-1"><i class="bi bi-globe me-1"></i>Publik</span>
                        <?php else: ?>
                            <span class="badge badge-visibility-authenticated px-2 py-1"><i class="bi bi-people-fill me-1"></i>Mahasiswa</span>
                        <?php endif; ?>
                    </div>
                    <h6 class="fw-bold mb-2">
                        <a href="<?= $href('/document/' . (int)$d['id']) ?>" class="text-dark text-decoration-none">
                            <?= Security::e($d['title']) ?>
                        </a>
                    </h6>
                    <?php if (!empty($d['description'])): ?>
                        <p class="small text-muted mb-2">
                            <?= Security::e(mb_substr($d['description'], 0, 110)) ?><?= mb_strlen($d['description']) > 110 ? '&hellip;' : '' ?>
                        </p>
                    <?php endif; ?>
                    <div class="small text-muted d-flex flex-wrap gap-3 mb-3">
                        <span><i class="bi bi-tag me-1"></i><?= Security::e($d['category_name']) ?></span>
                        <span><i class="bi bi-hdd me-1"></i><?= View::formatBytes((int)$d['file_size']) ?></span>
                        <span><i class="bi bi-calendar3 me-1"></i><?= Security::e(explode(',', View::formatDate($d['created_at']))[0]) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 border-top pt-3 mt-auto">
                        <span class="brand-icon flex-shrink-0" style="width:34px;height:34px;border-radius:50%;font-size:.85rem;">
                            <?= Security::e(mb_strtoupper(mb_substr($d['owner_name'], 0, 1))) ?>
                        </span>
                        <div class="small lh-sm text-truncate">
                            <div class="fw-semibold text-dark text-truncate"><?= Security::e($d['owner_name']) ?></div>
                            <div class="text-muted text-truncate"><?= Security::e($d['owner_institution']) ?></div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <a href="<?= $href('/document/' . (int)$d['id']) ?>" class="btn btn-outline-primary btn-sm rounded-3 flex-fill">
                            <i class="bi bi-eye me-1"></i>Lihat Detail
                        </a>
                        <a href="<?= $href('/download/' . (int)$d['id']) ?>" class="btn btn-primary btn-sm rounded-3 flex-fill">
                            <i class="bi bi-download me-1"></i>Unduh
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($tot > 1): ?>
        <nav class="mt-4" aria-label="Navigasi halaman">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $cur <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $pageUrl(max(1, $cur - 1)) ?>" aria-label="Sebelumnya">&lsaquo;</a>
                </li>
                <?php for ($i = max(1, $cur - 2); $i <= min($tot, $cur + 2); $i++): ?>
                    <li class="page-item <?= $i === $cur ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $pageUrl($i) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $cur >= $tot ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $pageUrl(min($tot, $cur + 1)) ?>" aria-label="Berikutnya">&rsaquo;</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>
