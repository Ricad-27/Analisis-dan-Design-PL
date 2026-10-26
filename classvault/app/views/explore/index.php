<?php
// app/views/explore/index.php — /explore (terbuka untuk tamu tanpa login)
$categories ??= [];
$documents  ??= [];
$pagination ??= ['current_page' => 1, 'total_pages' => 1, 'total_items' => 0];
$search     ??= '';
$catId      ??= 0;
$sort       ??= 'date_desc';

$route      = 'explore';
$emptyTitle = 'Belum ada dokumen publik';
$emptyText  = 'Belum ada dokumen publik yang diunggah.';
?>
<div class="container py-4">
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1"><i class="bi bi-compass me-2 text-primary"></i>Jelajahi Dokumen</h2>
        <p class="text-muted mb-0">Temukan dokumen akademik publik dari berbagai institusi.</p>
    </div>

    <?php if (!$is_logged_in): ?>
        <div class="alert alert-info border-0 shadow-sm rounded-3 d-flex align-items-start gap-2" role="alert">
            <i class="bi bi-person-check-fill fs-5"></i>
            <div>
                <strong>Mode tamu &mdash; tidak perlu login.</strong>
                Anda dapat mencari, melihat detail, dan mengunduh seluruh dokumen berstatus <em>Publik</em>.
                Dokumen <em>Private</em> dan khusus mahasiswa tidak ditampilkan.
            </div>
        </div>
    <?php endif; ?>

    <?php require __DIR__ . '/_document_list.php'; ?>
</div>
