<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/controllers/ScannerController.php';
(new ScannerController())->index();
