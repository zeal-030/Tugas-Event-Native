<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/controllers/UserDashboardController.php';

(new UserDashboardController())->downloadTicket();
