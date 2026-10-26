<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <?php if ($is_logged_in): ?>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/<?= $is_admin ? 'admin' : 'dashboard' ?>" class="text-decoration-none text-muted"><?= $is_admin ? 'Panel Admin' : 'Dashboard' ?></a></li>
            <?php else: ?>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/" class="text-decoration-none text-muted">Beranda</a></li>
            <?php endif; ?>
            <?php if ($is_owner): ?>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/my-documents" class="text-decoration-none text-muted">File Saya</a></li>
            <?php else: ?>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/explore" class="text-decoration-none text-muted">Jelajahi Dokumen</a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active text-dark" aria-current="page"><?= Security::e($doc['title']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card card-cv p-4 p-md-5 border-0 shadow-sm mb-4">
                <div class="d-flex align-items-start gap-3 mb-4 border-bottom pb-4">
                    <div class="brand-icon" style="width: 52px; height: 52px; border-radius: 12px; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge bg-light text-dark border fw-medium px-2 py-1">
                                <?= Security::e($doc['category_name']) ?>
                            </span>
                            <?php if ($doc['visibility'] === 'private'): ?>
                                <span class="badge badge-visibility-private px-2 py-1"><i class="bi bi-lock-fill me-1"></i>Private</span>
                            <?php elseif ($doc['visibility'] === 'authenticated'): ?>
                                <span class="badge badge-visibility-authenticated px-2 py-1"><i class="bi bi-people-fill me-1"></i>Khusus Mahasiswa</span>
                            <?php else: ?>
                                <span class="badge badge-visibility-public px-2 py-1"><i class="bi bi-globe me-1"></i>Publik</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="fw-bold text-dark mb-1"><?= Security::e($doc['title']) ?></h3>
                        <div class="text-muted small">
                            Diunggah oleh <strong><?= Security::e($doc['owner_name']) ?></strong> &bull; <?= Security::e($doc['owner_institution']) ?>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2">Deskripsi Dokumen</h6>
                    <?php if (!empty($doc['description'])): ?>
                        <div class="p-3 bg-light rounded-3 text-secondary" style="white-space: pre-line;">
                            <?= Security::e($doc['description']) ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small fst-italic mb-0">Tidak ada deskripsi atau catatan khusus untuk dokumen ini.</p>
                    <?php endif; ?>
                </div>

                <!-- Aksi Utama -->
                <div class="d-flex flex-wrap gap-2 pt-3 border-top">
                    <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/download/<?= (int)$doc['id'] ?>" 
                       class="btn btn-primary rounded-3 px-4 py-2 d-inline-flex align-items-center gap-2 fw-semibold">
                        <i class="bi bi-download"></i> Unduh Dokumen (<?= View::formatBytes((int)$doc['file_size']) ?>)
                    </a>

                    <?php if ($is_owner): ?>
                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/document/<?= (int)$doc['id'] ?>/edit" 
                           class="btn btn-outline-secondary rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-pencil-square"></i> Edit Metadata
                        </a>
                        <button type="button" class="btn btn-outline-danger rounded-3 px-3 py-2" 
                                data-bs-toggle="modal" data-bs-target="#deleteDetailModal">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Informasi Metadata -->
        <div class="col-lg-4">
            <div class="card card-cv p-4 border-0 shadow-sm">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Rincian Berkas</h6>
                <div class="list-group list-group-flush small">
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Nama Berkas Asli</span>
                        <span class="fw-medium text-dark text-truncate ms-2" style="max-width: 170px;" title="<?= Security::e($doc['original_filename']) ?>">
                            <?= Security::e($doc['original_filename']) ?>
                        </span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Ukuran Berkas</span>
                        <span class="fw-medium text-dark"><?= View::formatBytes((int)$doc['file_size']) ?></span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Tipe MIME</span>
                        <span class="fw-medium text-dark"><?= Security::e($doc['mime_type']) ?></span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Tanggal Unggah</span>
                        <span class="fw-medium text-dark text-end"><?= View::formatDate($doc['created_at']) ?></span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Pembaruan Terakhir</span>
                        <span class="fw-medium text-dark text-end"><?= View::formatDate($doc['updated_at']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($is_owner): ?>
    <!-- Modal Hapus -->
    <div class="modal fade" id="deleteDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">Konfirmasi Hapus Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-muted mb-0">Apakah Anda yakin ingin menghapus <strong>"<?= Security::e($doc['title']) ?>"</strong>? Dokumen ini tidak akan dapat diunduh kembali.</p>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <form action="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/document/<?= (int)$doc['id'] ?>/delete" method="POST" class="m-0">
                        <?= $csrf_field ?>
                        <button type="submit" class="btn btn-danger rounded-3 px-3">Hapus Dokumen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
