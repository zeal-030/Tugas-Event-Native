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
    if ($role === 'admin') {
        $target = BASE_URL . '/admin/dashboard.php';
    } elseif ($role === 'petugas') {
        $target = BASE_URL . '/petugas/dashboard.php';
    } else {
        $target = BASE_URL . '/user/dashboard.php';
    }
    header("Location: $target");
    exit;
}

/**
 * Wajib role petugas
 */
function requirePetugas(): void {
    requireLogin();
    if (currentRole() !== 'petugas') {
        redirectByRole(currentRole());
    }
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
 * Wajib role admin (Super Admin)
 */
function requireAdmin(): void {
    requireLogin();
    if (currentRole() !== 'admin') {
        redirectByRole(currentRole());
    }
}

/**
 * Wajib role staff (Admin atau Petugas) — Untuk Check-in/Scanner
 */
function requireStaff(): void {
    requireLogin();
    if (!in_array(currentRole(), ['admin', 'petugas'])) {
        redirectByRole(currentRole());
    }
}

/**
 * Wajib role admin saja (bukan petugas)
 */
function requireSuperAdmin(): void {
    requireLogin();
    if (currentRole() !== 'admin') {
        redirectByRole(currentRole());
    }
}

/**
 * Wajib role user biasa
 */
function requireUser(): void {
    requireLogin();
    if (currentRole() !== 'user') {
        redirectByRole(currentRole());
    }
}
