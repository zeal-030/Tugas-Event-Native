<?php
/**
 * Authentication Helpers
 * Semua fungsi terkait session dan otorisasi dikumpulkan di sini
 */

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['login']) && $_SESSION['login'] === true;
}

function currentRole(): string {
    return $_SESSION['role'] ?? '';
}

function currentUser(): array {
    return [
        'id'   => $_SESSION['id_user'] ?? null,
        'nama' => $_SESSION['nama']    ?? '',
        'role' => $_SESSION['role']    ?? '',
    ];
}

/**
 * Redirect user berdasarkan role mereka
 */
function redirectByRole(string $role): void {
    $target = in_array($role, ADMIN_ROLES)
        ? BASE_URL . '/admin/dashboard.php'
        : BASE_URL . '/user/dashboard.php';
    header("Location: $target");
    exit;
}

/**
 * Wajib login — jika tidak, redirect ke login
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Wajib role admin atau petugas — jika tidak, redirect ke login
 */
function requireAdmin(): void {
    requireLogin();
    if (!in_array(currentRole(), ADMIN_ROLES)) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Wajib role admin saja (bukan petugas)
 */
function requireSuperAdmin(): void {
    requireLogin();
    if (currentRole() !== 'admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    }
}

/**
 * Wajib role user biasa
 */
function requireUser(): void {
    requireLogin();
    if (currentRole() !== 'user') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    }
}
