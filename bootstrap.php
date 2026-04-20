<?php
/**
 * Bootstrap — dimuat di setiap public entry point
 * Urutan penting: constants → database → auth
 */
require_once __DIR__ . '/app/config/constants.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/config/auth.php';
