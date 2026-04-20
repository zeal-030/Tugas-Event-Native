<?php
/**
 * Backward-compatible wrapper — sidebar lama diteruskan ke layout baru
 * File ini dipertahankan agar halaman yang masih menggunakan include('../includes/sidebar.php')
 * tetap berfungsi selama proses migrasi.
 */
require_once __DIR__ . '/../app/views/layouts/sidebar.php';
