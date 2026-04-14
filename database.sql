-- 1. Database
CREATE DATABASE IF NOT EXISTS event_tiket;
USE event_tiket;

-- 2. Tabel users
CREATE TABLE users (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','petugas','admin') NOT NULL DEFAULT 'user'
);

-- 3. Tabel venue
CREATE TABLE venue (
  id_venue INT AUTO_INCREMENT PRIMARY KEY,
  nama_venue VARCHAR(100) NOT NULL,
  alamat TEXT,
  kapasitas INT NOT NULL
);

-- 4. Tabel voucher
CREATE TABLE voucher (
  id_voucher INT AUTO_INCREMENT PRIMARY KEY,
  kode_voucher VARCHAR(20) NOT NULL UNIQUE,
  potongan INT NOT NULL,
  kuota INT NOT NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
);

-- 5. Tabel event
CREATE TABLE event (
  id_event INT AUTO_INCREMENT PRIMARY KEY,
  nama_event VARCHAR(150) NOT NULL,
  tanggal DATE NOT NULL,
  id_venue INT NOT NULL,
  FOREIGN KEY (id_venue) REFERENCES venue(id_venue) ON DELETE CASCADE
);

-- 6. Tabel tiket
CREATE TABLE tiket (
  id_tiket INT AUTO_INCREMENT PRIMARY KEY,
  id_event INT NOT NULL,
  nama_tiket VARCHAR(50) NOT NULL,
  harga INT NOT NULL,
  kuota INT NOT NULL,
  FOREIGN KEY (id_event) REFERENCES event(id_event) ON DELETE CASCADE
);

-- 7. Tabel orders
CREATE TABLE orders (
  id_order INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  tanggal_order DATETIME DEFAULT CURRENT_TIMESTAMP,
  total INT NOT NULL DEFAULT 0,
  status ENUM('pending','paid','cancel') NOT NULL DEFAULT 'pending',
  id_voucher INT DEFAULT NULL,
  FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_voucher) REFERENCES voucher(id_voucher) ON DELETE SET NULL
);

-- 8. Tabel order_detail
CREATE TABLE order_detail (
  id_detail INT AUTO_INCREMENT PRIMARY KEY,
  id_order INT NOT NULL,
  id_tiket INT NOT NULL,
  qty INT NOT NULL,
  subtotal INT NOT NULL,
  FOREIGN KEY (id_order) REFERENCES orders(id_order) ON DELETE CASCADE,
  FOREIGN KEY (id_tiket) REFERENCES tiket(id_tiket) ON DELETE CASCADE
);

-- 9. Tabel attendee
CREATE TABLE attendee (
  id_attendee INT AUTO_INCREMENT PRIMARY KEY,
  id_detail INT NOT NULL,
  kode_tiket VARCHAR(50) NOT NULL UNIQUE,
  status_checkin ENUM('belum','sudah') NOT NULL DEFAULT 'belum',
  waktu_checkin DATETIME DEFAULT NULL,
  FOREIGN KEY (id_detail) REFERENCES order_detail(id_detail) ON DELETE CASCADE
);

-- Default Data
INSERT INTO users (nama, email, password, role) VALUES 
('Admin', 'admin@event.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), -- password: password
('User', 'user@event.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'); -- password: password
