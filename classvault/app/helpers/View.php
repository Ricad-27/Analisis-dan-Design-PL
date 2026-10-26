<?php
/**
 * ClassVault - View Renderer
 * Merender view template dengan layout konsisten, perlindungan XSS, dan data global.
 */

class View {
    /**
     * Escape output untuk mencegah XSS
     */
    public static function e(?string $str): string {
        return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Alias formatBytes dengan nama lebih singkat
     */
    public static function formatSize(int $bytes, int $precision = 1): string {
        return self::formatBytes($bytes, $precision);
    }

    /**
     * Emoji ikon berdasarkan ekstensi file
     */
    public static function fileIcon(string $ext): string {
        return match (strtolower(trim($ext, '.'))) {
            'pdf'              => '📄',
            'doc', 'docx'     => '📝',
            'xls', 'xlsx'     => '📊',
            'ppt', 'pptx'     => '📑',
            'txt'             => '📃',
            'zip', 'rar'      => '🗜',
            'jpg', 'jpeg',
            'png', 'gif',
            'webp'            => '🖼',
            default           => '📁',
        };
    }

    /**
     * Render view template
     * 
     * @param string $templatePath Contoh: 'home/index' atau 'auth/login'
     * @param array $data Variabel yang dilewatkan ke tampilan
     * @param string|null $layout Nama layout di app/views/layouts/ (default 'main')
     */
    public static function render(string $templatePath, array $data = [], ?string $layout = 'main'): void {
        $viewFile = APP_PATH . '/views/' . ltrim($templatePath, '/') . '.php';
        
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "Kesalahan Tampilan: File template '{$templatePath}' tidak ditemukan.";
            return;
        }

        $config = require APP_PATH . '/config/config.php';

        // Data bawaan untuk seluruh view
        $defaultData = [
            'app_name'       => $config['app_name'],
            'base_url'       => $config['app_url'],
            'current_user'   => Session::user(),
            'is_logged_in'   => Session::isLoggedIn(),
            'is_admin'       => Session::isAdmin(),
            'is_student'     => Session::isStudent(),
            'csrf_token'     => Security::csrfToken(),
            'csrf_field'     => Security::csrfField(),
            'flashes'        => Session::getFlashes(),
            'page_title'     => $config['app_name'],
        ];

        $mergedData = array_merge($defaultData, $data);
        extract($mergedData, EXTR_SKIP);

        // Tangkap isi konten view
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Jika menggunakan layout
        if ($layout !== null) {
            $layoutFile = APP_PATH . '/views/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
                return;
            }
        }

        echo $content;
    }

    /**
     * Helper redirect dengan opsional flash message
     */
    public static function redirect(string $path, ?string $flashType = null, ?string $flashMessage = null): void {
        $config = require APP_PATH . '/config/config.php';
        $baseUrl = $config['app_url'];

        if ($flashType !== null && $flashMessage !== null) {
            Session::setFlash($flashType, $flashMessage);
        }

        $targetUrl = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : rtrim($baseUrl, '/') . '/' . ltrim($path, '/');

        header("Location: {$targetUrl}");
        exit;
    }

    /**
     * Format ukuran byte ke string yang mudah dibaca (KB, MB, GB)
     */
    public static function formatBytes(int $bytes, int $precision = 1): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Format tanggal Indonesia
     */
    public static function formatDate(?string $datetime): string {
        if (empty($datetime)) {
            return '-';
        }
        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return '-';
        }
        
        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        $day = date('j', $timestamp);
        $month = $months[(int)date('n', $timestamp)];
        $year = date('Y', $timestamp);
        $time = date('H:i', $timestamp);

        return "{$day} {$month} {$year}, {$time}";
    }
}
