<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/controllers/EventController.php';
(new EventController())->index();
