-- ============================================================
-- CLASSVAULT DATABASE SCHEMA
-- Target Database: classvault_db
-- Compatibility: MySQL 5.7+ / MariaDB 10.3+
-- Dapat diimpor langsung melalui phpMyAdmin atau CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS `classvault_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `classvault_db`;

-- ------------------------------------------------------------
-- 1. TABEL USERS
-- Menyimpan data akun mahasiswa dan admin
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `institution` VARCHAR(100) NOT NULL,
    `institution_normalized` VARCHAR(100) NOT NULL,
    `nim` VARCHAR(50) NOT NULL,
    `nim_normalized` VARCHAR(50) NOT NULL,
    `program_study` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    `status` ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_institution_nim` (`institution_normalized`, `nim_normalized`),
    KEY `idx_role_status` (`role`, `status`),
    KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. TABEL CATEGORIES
-- Kategori dokumen akademik
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_category_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. SEED KATEGORI AWAL
-- Sesuai spesifikasi Bagian 6.A
-- ------------------------------------------------------------
INSERT INTO `categories` (`name`) VALUES
    ('Tugas Kuliah'),
    ('Laporan Praktikum'),
    ('Proposal'),
    ('Catatan'),
    ('Materi Belajar'),
    ('Lainnya')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ------------------------------------------------------------
-- 4. TABEL DOCUMENTS
-- Menyimpan metadata dokumen akademik
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `documents` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `storage_filename` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL,
    `visibility` ENUM('private', 'authenticated', 'public') NOT NULL DEFAULT 'private',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    KEY `idx_owner_id` (`owner_id`),
    KEY `idx_category_id` (`category_id`),
    KEY `idx_visibility` (`visibility`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_deleted_at` (`deleted_at`),
    FULLTEXT KEY `ft_search` (`title`, `description`),
    CONSTRAINT `fk_documents_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_documents_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. TABEL LOGIN_ATTEMPTS
-- Perlindungan brute-force login sesuai Bagian 4 & 9
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `identifier` VARCHAR(150) NOT NULL,
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_ip_attempt` (`ip_address`, `attempted_at`),
    KEY `idx_identifier_attempt` (`identifier`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
