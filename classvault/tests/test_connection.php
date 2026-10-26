<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';

try {
    $pdo = Database::getConnection();
    echo "=== PENGUJIAN DATABASE CLASSVAULT ===" . PHP_EOL;
    
    // Uji Kategori
    $stmtCats = $pdo->query("SELECT name FROM categories");
    $cats = $stmtCats->fetchAll(PDO::FETCH_COLUMN);
    echo "Kategori Terdaftar (" . count($cats) . "): " . implode(', ', $cats) . PHP_EOL;

    // Uji Tabel Users
    $stmtUsers = $pdo->query("SELECT id, name, institution, nim, role, status FROM users");
    $users = $stmtUsers->fetchAll();
    echo "Pengguna Terdaftar: " . count($users) . PHP_EOL;
    foreach ($users as $u) {
        echo "- [{$u['id']}] {$u['name']} ({$u['role']} - {$u['status']}) | {$u['institution']} - {$u['nim']}" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
