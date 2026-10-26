<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/my-documents" class="text-decoration-none text-muted">File Saya</a></li>
                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/document/<?= (int)$doc['id'] ?>" class="text-decoration-none text-muted"><?= Security::e($doc['title']) ?></a></li>
                    <li class="breadcrumb-item active text-dark" aria-current="page">Edit Metadata</li>
                </ol>
            </nav>

            <div class="card card-cv p-4 p-md-5 border-0 shadow-sm">
                <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                    <div class="brand-icon" style="width: 44px; height: 44px; border-radius: 10px; font-size: 1.25rem;">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Edit Metadata Dokumen</h4>
                        <div class="text-muted small">Perbarui judul, kategori, tingkat privasi, atau unggah file revisi.</div>
                    </div>
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

                <form action="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/document/<?= (int)$doc['id'] ?>/edit" method="POST" enctype="multipart/form-data" novalidate>
                    <?= $csrf_field ?>

                    <!-- Judul / Nama Dokumen -->
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold text-dark">
                            Judul / Nama Dokumen <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= Security::e($doc['title'] ?? '') ?>" required>
                    </div>

                    <!-- Kategori & Privasi -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="category_id" class="form-label fw-semibold text-dark">
                                Kategori Dokumen <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>" <?= ((int)$doc['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                        <?= Security::e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label for="visibility" class="form-label fw-semibold text-dark">
                                Pengaturan Privasi <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="visibility" name="visibility" required>
                                <option value="private" <?= ($doc['visibility'] === 'private') ? 'selected' : '' ?>>
                                    🔒 Private (Hanya Saya)
                                </option>
                                <option value="authenticated" <?= ($doc['visibility'] === 'authenticated') ? 'selected' : '' ?>>
                                    👥 Pengguna Login (Mahasiswa Lain)
                                </option>
                                <option value="public" <?= ($doc['visibility'] === 'public') ? 'selected' : '' ?>>
                                    🌐 Publik (Siapa Saja)
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold text-dark d-flex justify-content-between">
                            <span>Deskripsi / Catatan Tambahan</span>
                            <span class="text-muted fw-normal small">(Opsional)</span>
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= Security::e($doc['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Unggah File Revisi Pengganti (Opsional) -->
                    <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                        <label for="revision_file" class="form-label fw-semibold text-dark d-flex justify-content-between">
                            <span>Unggah File Revisi Pengganti</span>
                            <span class="badge bg-secondary-subtle text-secondary border">Opsional</span>
                        </label>
                        <div class="small text-muted mb-2">
                            Berkas saat ini: <strong><?= Security::e($doc['original_filename']) ?></strong> (<?= View::formatBytes((int)$doc['file_size']) ?>)
                        </div>
                        <input class="form-control" type="file" id="revision_file" name="revision_file">
                        <div class="form-text small mt-1 text-muted">
                            Biarkan kosong jika tidak ingin mengganti isi berkas yang sudah ada.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/document/<?= (int)$doc['id'] ?>" class="btn btn-light rounded-3 px-3">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
