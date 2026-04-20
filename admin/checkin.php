<?php
/**
 * Admin Checkin — update ke bootstrap baru
 */
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/controllers/ScannerController.php';

// Checkin.php menggunakan ScannerController yang sama karena fungsinya identik
(new ScannerController())->index();
