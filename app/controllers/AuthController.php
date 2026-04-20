<?php
/**
 * AuthController — Handle Login, Logout, Register
 */
class AuthController {
    private $userModel;

    public function __construct() {
        require_once __DIR__ . '/../models/UserModel.php';
        $this->userModel = new UserModel();
    }

    public function login(): void {
        startSession();

        // Jika sudah login, redirect sesuai role
        if (isLoggedIn()) {
            redirectByRole(currentRole());
        }

        $error = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['login']   = true;
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['nama']    = $user['nama'];
                $_SESSION['role']    = $user['role'];
                redirectByRole($user['role']);
            }

            $error = true;
        }

        $registered = isset($_GET['registered']);
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function register(): void {
        startSession();

        if (isLoggedIn()) {
            redirectByRole(currentRole());
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
            $nama  = trim($_POST['nama']             ?? '');
            $email = trim($_POST['email']            ?? '');
            $pass  = $_POST['password']              ?? '';
            $conf  = $_POST['confirm_password']      ?? '';

            if ($this->userModel->emailExists($email)) {
                $error = 'Email sudah terdaftar!';
            } elseif ($pass !== $conf) {
                $error = 'Konfirmasi password tidak sesuai!';
            } else {
                $hashed = password_hash($pass, PASSWORD_DEFAULT);
                if ($this->userModel->create($nama, $email, $hashed, 'user')) {
                    header('Location: ' . BASE_URL . '/login.php?registered=1');
                    exit;
                }
                $error = 'Gagal mendaftar, coba lagi.';
            }
        }

        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function logout(): void {
        startSession();
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}
