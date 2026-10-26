<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::e($page_title) ?> - ClassVault</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom ClassVault Stylesheet -->
    <link rel="stylesheet" href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/assets/css/style.css">
</head>
<body>

    <!-- Header / Navigasi -->
    <nav class="navbar navbar-expand-lg navbar-cv sticky-top">
        <div class="container">
            <a class="navbar-brand navbar-brand-cv" href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/">
                <span class="brand-icon"><i class="bi bi-folder-fill"></i></span>
                <span>ClassVault</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#cvNavbar" aria-controls="cvNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="cvNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-medium" href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-medium" href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/explore">
                            <i class="bi bi-compass me-1"></i>Jelajahi Dokumen
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <?php if ($is_logged_in): ?>
                        <?php if ($is_student): ?>
                            <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/upload" class="btn btn-primary btn-sm px-3 rounded-3 d-none d-md-inline-flex align-items-center gap-1">
                                <i class="bi bi-cloud-arrow-up"></i> Unggah File
                            </a>
                            <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/dashboard" class="btn btn-outline-primary btn-sm px-3 rounded-3">
                                Dashboard
                            </a>
                        <?php elseif ($is_admin): ?>
                            <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/admin" class="btn btn-outline-primary btn-sm px-3 rounded-3">
                                Panel Admin
                            </a>
                        <?php endif; ?>

                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle rounded-3 border d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                                <span class="d-none d-sm-inline"><?= Security::e($current_user['name'] ?? 'Akun') ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
                                <li class="px-3 py-2 border-bottom">
                                    <div class="small fw-bold text-dark"><?= Security::e($current_user['name'] ?? '') ?></div>
                                    <div class="text-muted small"><?= Security::e($current_user['institution'] ?? '') ?></div>
                                    <span class="badge bg-secondary-subtle text-secondary border mt-1" style="font-size: 0.7rem;">
                                        <?= $is_admin ? 'Administrator' : 'Mahasiswa' ?>
                                    </span>
                                </li>
                                <?php if ($is_student): ?>
                                    <li><a class="dropdown-item py-2" href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/my-documents"><i class="bi bi-folder2 me-2"></i>File Saya</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/shared-documents"><i class="bi bi-people me-2"></i>File Dibagikan</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/profile"><i class="bi bi-person-gear me-2"></i>Profil</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/change-password"><i class="bi bi-key me-2"></i>Ubah Password</a></li>
                                <?php elseif ($is_admin): ?>
                                    <li><a class="dropdown-item py-2" href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/admin/users"><i class="bi bi-people-fill me-2"></i>Kelola Pengguna</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/logout" method="POST" class="m-0 p-0">
                                        <?= $csrf_field ?>
                                        <button type="submit" class="dropdown-item py-2 text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Keluar (Logout)
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/login" class="btn btn-outline-primary btn-sm px-3 rounded-3">Masuk</a>
                        <a href="<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/register" class="btn btn-primary btn-sm px-3 rounded-3">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container mt-3">
        <?php if (!empty($flashes)): ?>
            <?php foreach ($flashes as $type => $message): ?>
                <?php
                    $alertClass = match($type) {
                        'success' => 'alert-success',
                        'error', 'danger' => 'alert-danger',
                        'warning' => 'alert-warning',
                        default => 'alert-info'
                    };
                    $iconClass = match($type) {
                        'success' => 'bi-check-circle-fill',
                        'error', 'danger' => 'bi-exclamation-triangle-fill',
                        'warning' => 'bi-exclamation-circle-fill',
                        default => 'bi-info-circle-fill'
                    };
                ?>
                <div class="alert <?= $alertClass ?> alert-dismissible fade show rounded-3 border-0 shadow-sm d-flex align-items-center" role="alert">
                    <i class="bi <?= $iconClass ?> me-2 fs-5"></i>
                    <div><?= Security::e($message) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <main class="flex-grow-1">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-1 fw-medium text-dark">ClassVault &copy; <?= date('Y') ?> &mdash; Penyimpanan Dokumen Akademik Pribadi</p>
            <p class="mb-0 text-muted small">Aman, Terstruktur, dan Terkendali.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
