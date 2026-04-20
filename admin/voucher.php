<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/controllers/VoucherController.php';
(new VoucherController())->index();
