<?php
require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel {

    public function findByEmail(string $email): ?array {
        $email = $this->escape($email);
        return $this->fetchOne("SELECT * FROM users WHERE email = '$email'");
    }

    public function findById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM users WHERE id_user = $id");
    }

    public function emailExists(string $email): bool {
        $email  = $this->escape($email);
        $result = mysqli_query($this->conn, "SELECT id_user FROM users WHERE email = '$email'");
        return mysqli_num_rows($result) > 0;
    }

    public function create(string $nama, string $email, string $password, string $role = 'user'): bool {
        $nama     = $this->escape($nama);
        $email    = $this->escape($email);
        $password = $this->escape($password);
        $role     = $this->escape($role);
        return $this->execute(
            "INSERT INTO users (nama, email, password, role) VALUES ('$nama','$email','$password','$role')"
        );
    }

    public function countByRole(string $role = 'user'): int {
        $role = $this->escape($role);
        $row  = $this->fetchOne("SELECT COUNT(*) as t FROM users WHERE role = '$role'");
        return (int)($row['t'] ?? 0);
    }

    public function update(int $id, string $nama, string $email, ?string $password = null): bool {
        $nama  = $this->escape($nama);
        $email = $this->escape($email);
        $sql   = "UPDATE users SET nama = '$nama', email = '$email'";
        if ($password !== null) {
            $password = $this->escape($password);
            $sql .= ", password = '$password'";
        }
        $sql .= " WHERE id_user = $id";
        return $this->execute($sql);
    }
}
