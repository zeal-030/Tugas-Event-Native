<?php
/**
 * Konstanta global aplikasi
 */

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

/**
 * Deteksi BASE_URL dengan cara yang stabil di semua kedalaman folder.
 * __DIR__ selalu = .../event-ku/app/config
 * dirname(__DIR__, 2) = .../event-ku  (naik 2 level)
 * DOCUMENT_ROOT      = .../htdocs
 * relative           = /event-ku
 */
$project_root = dirname(__DIR__, 2);
$doc_root     = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$relative     = '';

if ($doc_root && str_starts_with($project_root, $doc_root)) {
    $relative = substr($project_root, strlen($doc_root));
    $relative = '/' . ltrim(str_replace('\\', '/', $relative), '/');
    $relative = rtrim($relative, '/');
}

define('BASE_URL',    $protocol . '://' . $host . $relative);
define('APP_NAME',    'E-Tiket');
define('ADMIN_ROLES', ['admin', 'petugas']);
define('UPLOAD_DIR',  $project_root . '/assets/img/events/');
