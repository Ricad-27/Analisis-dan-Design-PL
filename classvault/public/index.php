<?php
/**
 * ClassVault - Front Controller
 * Seluruh HTTP request dialihkan ke file ini.
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/Security.php';
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/View.php';
require_once __DIR__ . '/../app/helpers/Router.php';

// Inisialisasi session dengan pengaturan keamanan cookie
Session::init();

// --- DEFINISI RUTE APLIKASI ---

// Rute Publik
Router::get('/', ['HomeController', 'index']);

// Rute Autentikasi Mahasiswa & Admin
Router::get('/register', ['AuthController', 'showRegister']);
Router::post('/register', ['AuthController', 'register']);
Router::get('/login', ['AuthController', 'showLogin']);
Router::post('/login', ['AuthController', 'login']);
Router::post('/logout', ['AuthController', 'logout']);
Router::get('/logout', ['AuthController', 'logout']);

// Rute Dashboard Privat Mahasiswa & Admin
Router::get('/dashboard', ['StudentController', 'dashboard']);
Router::get('/admin', ['AdminController', 'dashboard']);
Router::get('/admin/users', ['AdminController', 'users']);
Router::get('/admin/users/{id}', ['AdminController', 'userDetail']);
Router::post('/admin/users/{id}/status', ['AdminController', 'toggleStatus']);
Router::post('/admin/users/{id}/reset-password', ['AdminController', 'resetPassword']);

// Rute Pengelolaan Dokumen Mahasiswa (Tahap 4)
Router::get('/my-documents', ['DocumentController', 'myDocuments']);
Router::get('/upload', ['DocumentController', 'showUpload']);
Router::post('/upload', ['DocumentController', 'upload']);
Router::get('/document/{id}', ['DocumentController', 'detail']);
Router::get('/document/{id}/edit', ['DocumentController', 'showEdit']);
Router::post('/document/{id}/edit', ['DocumentController', 'edit']);
Router::post('/document/{id}/delete', ['DocumentController', 'delete']);

// Endpoint Unduhan Terproteksi
Router::get('/download/{id}', ['DocumentController', 'download']);

// Rute Tahap 5: Jelajahi Dokumen & File Dibagikan
Router::get('/explore', ['ExploreController', 'explore']);
Router::get('/shared-documents', ['ExploreController', 'shared']);

// Rute Tahap 5: Profil & Ubah Password
Router::get('/profile', ['ProfileController', 'showProfile']);
Router::post('/profile', ['ProfileController', 'updateProfile']);
Router::get('/change-password', ['ProfileController', 'showChangePassword']);
Router::post('/change-password', ['ProfileController', 'changePassword']);

// Jalankan Dispatcher
Router::dispatch();
