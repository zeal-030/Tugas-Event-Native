<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
(new AuthController())->logout();
