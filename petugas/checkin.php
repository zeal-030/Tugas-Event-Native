<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/controllers/ScannerController.php';

// Petugas Checkin
(new ScannerController())->list();
