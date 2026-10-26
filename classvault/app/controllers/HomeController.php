<?php
/**
 * ClassVault - HomeController
 * Menangani halaman utama publik
 */

class HomeController {
    public function index(): void {
        View::render('home/index', [
            'page_title' => 'Penyimpanan Dokumen Akademik Pribadi'
        ]);
    }
}
