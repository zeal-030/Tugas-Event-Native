<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/controllers/LaporanController.php';
(new LaporanController())->pdf();
