<div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1">File Saya</h2>
            <p class="text-muted mb-0">Kelola seluruh dokumen dan berkas akademik yang Anda miliki.</p>
        </div>
        <div>
            <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/upload" class="btn btn-primary rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2">
                <i class="bi bi-cloud-arrow-up-fill"></i> Unggah File Baru
            </a>
        </div>
    </div>

    <!-- Filter & Pencarian Bar -->
    <div class="card card-cv p-3 border-0 shadow-sm mb-4">
        <form action="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/my-documents" method="GET" class="row g-2 align-items-center">
            <!-- Search Input -->
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" name="q" 
                           placeholder="Cari nama atau deskripsi..." 
                           value="<?= Security::e($filters['search']) ?>">
                </div>
            </div>

            <!-- Filter Kategori -->
            <div class="col-sm-6 col-md-3">
                <select class="form-select" name="category">
                    <option value="0">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= ((int)$filters['category'] === (int)$cat['id']) ? 'selected' : '' ?>>
                            <?= Security::e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Privasi -->
            <div class="col-sm-6 col-md-2">
                <select class="form-select" name="visibility">
                    <option value="">Semua Privasi</option>
                    <option value="private" <?= ($filters['visibility'] === 'private') ? 'selected' : '' ?>>🔒 Private</option>
                    <option value="authenticated" <?= ($filters['visibility'] === 'authenticated') ? 'selected' : '' ?>>👥 Mahasiswa</option>
                    <option value="public" <?= ($filters['visibility'] === 'public') ? 'selected' : '' ?>>🌐 Publik</option>
                </select>
            </div>

            <!-- Pengurutan -->
            <div class="col-sm-8 col-md-2">
                <select class="form-select" name="sort">
                    <option value="date_desc" <?= ($filters['sort'] === 'date_desc') ? 'selected' : '' ?>>Terbaru</option>
                    <option value="date_asc" <?= ($filters['sort'] === 'date_asc') ? 'selected' : '' ?>>Terlama</option>
                    <option value="name_asc" <?= ($filters['sort'] === 'name_asc') ? 'selected' : '' ?>>Nama (A-Z)</option>
                    <option value="name_desc" <?= ($filters['sort'] === 'name_desc') ? 'selected' : '' ?>>Nama (Z-A)</option>
                    <option value="size_desc" <?= ($filters['sort'] === 'size_desc') ? 'selected' : '' ?>>Ukuran Terbesar</option>
                </select>
            </div>

            <div class="col-sm-4 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100 rounded-3" title="Terapkan Filter">
                    <i class="bi bi-funnel-fill"></i>
                </button>
                <?php if ($filters['search'] !== '' || $filters['category'] > 0 || $filters['visibility'] !== '' || $filters['sort'] !== 'date_desc'): ?>
                    <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/my-documents" class="btn btn-light rounded-3 text-danger" title="Reset Filter">
                        <i class="bi bi-x-circle"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Daftar Dokumen -->
    <?php if (empty($documents)): ?>
        <div class="card card-cv p-5 border-0 shadow-sm text-center">
            <div class="empty-state p-0">
                <div class="empty-state-icon">
                    <i class="bi bi-folder-x"></i>
                </div>
                <h4 class="fw-bold text-dark">Tidak Ada Dokumen yang Ditemukan</h4>
                <p class="text-muted small mb-3">
                    <?php if ($filters['search'] !== '' || $filters['category'] > 0 || $filters['visibility'] !== ''): ?>
                        Tidak ada dokumen yang cocok dengan kata kunci atau filter pencarian Anda.
                    <?php else: ?>
                        Anda belum mengunggah dokumen akademik. Mulai simpan file kuliah Anda sekarang.
                    <?php endif; ?>
                </p>
                <div>
                    <?php if ($filters['search'] !== '' || $filters['category'] > 0 || $filters['visibility'] !== ''): ?>
                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/my-documents" class="btn btn-outline-primary rounded-3 px-3 me-2">
                            Reset Filter
                        </a>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/upload" class="btn btn-primary rounded-3 px-3">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Unggah File Baru
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Tampilan Desktop (Tabel) -->
        <div class="card card-cv border-0 shadow-sm overflow-hidden d-none d-md-block mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3 text-secondary small fw-semibold">Nama Dokumen</th>
                            <th class="py-3 text-secondary small fw-semibold">Kategori</th>
                            <th class="py-3 text-secondary small fw-semibold">Ukuran</th>
                            <th class="py-3 text-secondary small fw-semibold">Privasi</th>
                            <th class="py-3 text-secondary small fw-semibold">Diunggah</th>
                            <th class="pe-4 py-3 text-secondary small fw-semibold text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="brand-icon" style="width: 38px; height: 38px; border-radius: 8px; font-size: 1.1rem; flex-shrink: 0;">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <div class="text-truncate" style="max-width: 320px;">
                                            <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/document/<?= (int)$doc['id'] ?>" 
                                               class="text-dark fw-semibold text-decoration-none d-block text-truncate">
                                                <?= Security::e($doc['title']) ?>
                                            </a>
                                            <span class="small text-muted text-truncate d-block">
                                                <?= Security::e($doc['original_filename']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-medium px-2 py-1">
                                        <?= Security::e($doc['category_name']) ?>
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <?= View::formatBytes((int)$doc['file_size']) ?>
                                </td>
                                <td>
                                    <?php if ($doc['visibility'] === 'private'): ?>
                                        <span class="badge badge-visibility-private px-2 py-1">
                                            <i class="bi bi-lock-fill me-1"></i>Private
                                        </span>
                                    <?php elseif ($doc['visibility'] === 'authenticated'): ?>
                                        <span class="badge badge-visibility-authenticated px-2 py-1">
                                            <i class="bi bi-people-fill me-1"></i>Mahasiswa
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-visibility-public px-2 py-1">
                                            <i class="bi bi-globe me-1"></i>Publik
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?= View::formatDate($doc['created_at']) ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/download/<?= (int)$doc['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary rounded-start-3" title="Unduh File">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/document/<?= (int)$doc['id'] ?>" 
                                           class="btn btn-sm btn-outline-secondary" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/document/<?= (int)$doc['id'] ?>/edit" 
                                           class="btn btn-sm btn-outline-secondary" title="Edit Metadata">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-end-3" 
                                                data-bs-toggle="modal" data-bs-target="#deleteModal<?= (int)$doc['id'] ?>" title="Hapus Dokumen">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Modal Konfirmasi Hapus -->
                                    <div class="modal fade text-start" id="deleteModal<?= (int)$doc['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-dark">Hapus Dokumen</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <p class="text-muted mb-0">
                                                        Apakah Anda yakin ingin menghapus dokumen <strong>"<?= Security::e($doc['title']) ?>"</strong>? Dokumen ini akan dipindahkan ke arsip terhapus.
                                                    </p>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                                                    <form action="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/document/<?= (int)$doc['id'] ?>/delete" method="POST" class="m-0">
                                                        <?= $csrf_field ?>
                                                        <button type="submit" class="btn btn-danger rounded-3 px-3">
                                                            <i class="bi bi-trash me-1"></i>Hapus Dokumen
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tampilan Mobile (Kartu Responsif) -->
        <div class="d-md-none mb-4">
            <?php foreach ($documents as $doc): ?>
                <div class="card card-cv p-3 border-0 shadow-sm mb-3">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <div class="brand-icon" style="width: 40px; height: 40px; border-radius: 8px; font-size: 1.2rem; flex-shrink: 0;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/document/<?= (int)$doc['id'] ?>" 
                               class="text-dark fw-bold text-decoration-none d-block text-truncate">
                                <?= Security::e($doc['title']) ?>
                            </a>
                            <div class="small text-muted text-truncate"><?= Security::e($doc['original_filename']) ?></div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge bg-light text-dark border"><?= Security::e($doc['category_name']) ?></span>
                        <span class="small text-muted"><?= View::formatBytes((int)$doc['file_size']) ?></span>
                        <?php if ($doc['visibility'] === 'private'): ?>
                            <span class="badge badge-visibility-private"><i class="bi bi-lock-fill me-1"></i>Private</span>
                        <?php elseif ($doc['visibility'] === 'authenticated'): ?>
                            <span class="badge badge-visibility-authenticated"><i class="bi bi-people-fill me-1"></i>Mahasiswa</span>
                        <?php else: ?>
                            <span class="badge badge-visibility-public"><i class="bi bi-globe me-1"></i>Publik</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-top pt-2">
                        <span class="small text-muted"><?= View::formatDate($doc['created_at']) ?></span>
                        <div class="btn-group">
                            <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/download/<?= (int)$doc['id'] ?>" class="btn btn-sm btn-outline-primary" title="Unduh">
                                <i class="bi bi-download"></i>
                            </a>
                            <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/document/<?= (int)$doc['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModalMobile<?= (int)$doc['id'] ?>" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <!-- Modal Mobile -->
                        <div class="modal fade text-start" id="deleteModalMobile<?= (int)$doc['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom-0 pb-0">
                                        <h5 class="modal-title fw-bold text-dark">Hapus Dokumen</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-3">
                                        <p class="text-muted mb-0">Apakah Anda yakin ingin menghapus <strong>"<?= Security::e($doc['title']) ?>"</strong>?</p>
                                    </div>
                                    <div class="modal-footer border-top-0 pt-0">
                                        <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                                        <form action="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/document/<?= (int)$doc['id'] ?>/delete" method="POST" class="m-0">
                                            <?= $csrf_field ?>
                                            <button type="submit" class="btn btn-danger rounded-3 px-3">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    Menampilkan <strong><?= count($documents) ?></strong> dari <strong><?= (int)$pagination['total_items'] ?></strong> dokumen
                </div>
                <nav aria-label="Navigasi Halaman">
                    <ul class="pagination pagination-sm mb-0">
                        <?php
                            $queryParams = $_GET;
                            $buildUrl = function($p) use ($base_url, $queryParams) {
                                $queryParams['page'] = $p;
                                return htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') . '/my-documents?' . http_build_query($queryParams);
                            };
                        ?>
                        <li class="page-item <?= ($pagination['current_page'] <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-start-3" href="<?= $buildUrl($pagination['current_page'] - 1) ?>">Sebelumnya</a>
                        </li>
                        <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                            <li class="page-item <?= ($p === (int)$pagination['current_page']) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $buildUrl($p) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($pagination['current_page'] >= $pagination['total_pages']) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-end-3" href="<?= $buildUrl($pagination['current_page'] + 1) ?>">Selanjutnya</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
