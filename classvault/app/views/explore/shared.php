<?php
// app/views/explore/shared.php — /shared-documents (khusus pengguna login)
$categories ??= [];
$documents  ??= [];
$pagination ??= ['current_page' => 1, 'total_pages' => 1, 'total_items' => 0];
$search     ??= '';
$catId      ??= 0;
$sort       ??= 'date_desc';

$route      = 'shared-documents';
$emptyTitle = 'Belum ada file yang dibagikan';
$emptyText  = 'Belum ada pengguna lain yang membagikan dokumen kepada komunitas.';
?>
<div class="container py-4">
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1"><i class="bi bi-people me-2 text-primary"></i>File Dibagikan</h2>
        <p class="text-muted mb-0">Dokumen dari pengguna lain yang dibagikan kepada mahasiswa terdaftar dan publik.</p>
    </div>

    <?php require __DIR__ . '/_document_list.php'; ?>
</div>
