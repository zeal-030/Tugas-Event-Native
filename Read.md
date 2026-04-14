# 🧪 Latihan Uji Kompetensi (UK)
## Sistem Informasi Pemesanan Tiket Event Berbasis Web

---

## 📌 Informasi Umum

| Atribut | Keterangan |
|---|---|
| **Tema** | Sistem Informasi Pemesanan Tiket Event |
| **Platform** | Berbasis Web |
| **Teknologi** | PHP Native + MySQL + Bootstrap |
| **Jumlah Tugas** | 25 Tugas Utama + Bonus |

---

## 🗂️ Struktur Bagian

Proyek ini dibagi menjadi **10 Bagian (A–J)** ditambah **Bonus**, masing-masing mencakup aspek berbeda dari sistem:

| Bagian | Topik | Tugas |
|---|---|---|
| A | Persiapan Database | 1–3 |
| B | Sistem Login | 4–5 |
| C | CRUD Master Data (Admin) | 6–9 |
| D | Pemesanan Tiket (User) | 10–12 |
| E | Voucher & Pembayaran | 13–14 |
| F | Generate Tiket (Attendee) | 15–16 |
| G | Check-in Tiket | 17–18 |
| H | Dashboard & Laporan | 19–20 |
| I | Tampilan UI | 21 |
| J | Soal HOTS (Analisis & Pengembangan) | 22–25 |
| ⭐ | Bonus | — |

---

## 🔹 BAGIAN A: Persiapan Database

### Tugas 1 — Buat Database
Buat database dengan nama:
```sql
CREATE DATABASE event_tiket;
```

### Tugas 2 — Buat Tabel Sesuai ERD
Buat tabel-tabel berikut sesuai dengan ERD yang diberikan:

- `users`
- `venue`
- `event`
- `tiket`
- `orders`
- `order_detail`
- `voucher`
- `attendee`

### Tugas 3 — Tentukan Primary Key & Foreign Key
Tentukan:
- **Primary Key** untuk setiap tabel
- **Foreign Key** sesuai relasi pada ERD

📌 **Output:** Script SQL lengkap (`CREATE TABLE` + relasi antar tabel)

---

## 🗄️ Struktur Database (Berdasarkan ERD)

> Database: `event_tiket`

### Relasi Antar Tabel

```
venue ──< event ──< tiket ──< order_detail >── orders >── attendee
                                                  │
                                               users
                                                  │
                                              voucher
```

| Relasi | Keterangan |
|---|---|
| `venue` → `event` | Satu venue memiliki banyak event |
| `event` → `tiket` | Satu event memiliki banyak jenis tiket |
| `tiket` → `order_detail` | Satu tiket dapat ada di banyak order detail |
| `orders` → `order_detail` | Satu order memiliki banyak detail item |
| `orders` → `attendee` | Satu order menghasilkan banyak attendee |
| `users` → `orders` | Satu user dapat membuat banyak order |
| `voucher` → `orders` | Satu voucher dapat dipakai di banyak order |

---

### 📋 Detail Struktur Tabel

#### 1. Tabel `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_user` | INT | Primary Key |
| `nama` | VARCHAR(100) | Nama lengkap user |
| `email` | VARCHAR(100) | Email login |
| `password` | VARCHAR(255) | Password (hashed) |
| `role` | ENUM('user','petugas','admin') | Hak akses user |

```sql
CREATE TABLE users (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','petugas','admin') NOT NULL DEFAULT 'user'
);
```

---

#### 2. Tabel `venue`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_venue` | INT | Primary Key |
| `nama_venue` | VARCHAR(100) | Nama tempat |
| `alamat` | TEXT | Alamat lengkap |
| `kapasitas` | INT | Kapasitas maksimal |

```sql
CREATE TABLE venue (
  id_venue INT AUTO_INCREMENT PRIMARY KEY,
  nama_venue VARCHAR(100) NOT NULL,
  alamat TEXT,
  kapasitas INT NOT NULL
);
```

---

#### 3. Tabel `event`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_event` | INT | Primary Key |
| `nama_event` | VARCHAR(150) | Nama event |
| `tanggal` | DATE | Tanggal pelaksanaan |
| `id_venue` | INT | Foreign Key → `venue.id_venue` |

```sql
CREATE TABLE event (
  id_event INT AUTO_INCREMENT PRIMARY KEY,
  nama_event VARCHAR(150) NOT NULL,
  tanggal DATE NOT NULL,
  id_venue INT NOT NULL,
  FOREIGN KEY (id_venue) REFERENCES venue(id_venue)
);
```

---

#### 4. Tabel `tiket`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_tiket` | INT | Primary Key |
| `id_event` | INT | Foreign Key → `event.id_event` |
| `nama_tiket` | VARCHAR(50) | Jenis/nama tiket |
| `harga` | INT | Harga per tiket |
| `kuota` | INT | Jumlah kuota tersedia |

```sql
CREATE TABLE tiket (
  id_tiket INT AUTO_INCREMENT PRIMARY KEY,
  id_event INT NOT NULL,
  nama_tiket VARCHAR(50) NOT NULL,
  harga INT NOT NULL,
  kuota INT NOT NULL,
  FOREIGN KEY (id_event) REFERENCES event(id_event)
);
```

---

#### 5. Tabel `voucher`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_voucher` | INT | Primary Key |
| `kode_voucher` | VARCHAR(20) | Kode unik voucher |
| `potongan` | INT | Nominal potongan harga |
| `kuota` | INT | Batas penggunaan voucher |
| `status` | ENUM('aktif','nonaktif') | Status voucher |

```sql
CREATE TABLE voucher (
  id_voucher INT AUTO_INCREMENT PRIMARY KEY,
  kode_voucher VARCHAR(20) NOT NULL UNIQUE,
  potongan INT NOT NULL,
  kuota INT NOT NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
);
```

---

#### 6. Tabel `orders`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_order` | INT | Primary Key |
| `id_user` | INT | Foreign Key → `users.id_user` |
| `tanggal_order` | DATETIME | Waktu order dibuat |
| `total` | INT | Total pembayaran setelah diskon |
| `status` | ENUM('pending','paid','cancel') | Status pembayaran |
| `id_voucher` | INT | Foreign Key → `voucher.id_voucher` (nullable) |

```sql
CREATE TABLE orders (
  id_order INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  tanggal_order DATETIME DEFAULT CURRENT_TIMESTAMP,
  total INT NOT NULL DEFAULT 0,
  status ENUM('pending','paid','cancel') NOT NULL DEFAULT 'pending',
  id_voucher INT DEFAULT NULL,
  FOREIGN KEY (id_user) REFERENCES users(id_user),
  FOREIGN KEY (id_voucher) REFERENCES voucher(id_voucher)
);
```

---

#### 7. Tabel `order_detail`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_detail` | INT | Primary Key |
| `id_order` | INT | Foreign Key → `orders.id_order` |
| `id_tiket` | INT | Foreign Key → `tiket.id_tiket` |
| `qty` | INT | Jumlah tiket yang dibeli |
| `subtotal` | INT | Harga × qty |

```sql
CREATE TABLE order_detail (
  id_detail INT AUTO_INCREMENT PRIMARY KEY,
  id_order INT NOT NULL,
  id_tiket INT NOT NULL,
  qty INT NOT NULL,
  subtotal INT NOT NULL,
  FOREIGN KEY (id_order) REFERENCES orders(id_order),
  FOREIGN KEY (id_tiket) REFERENCES tiket(id_tiket)
);
```

---

#### 8. Tabel `attendee`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id_attendee` | INT | Primary Key |
| `id_detail` | INT | Foreign Key → `order_detail.id_detail` |
| `kode_tiket` | VARCHAR(50) | Kode tiket unik yang digenerate |
| `status_checkin` | ENUM('belum','sudah') | Status kehadiran |
| `waktu_checkin` | DATETIME | Waktu saat check-in dilakukan |

```sql
CREATE TABLE attendee (
  id_attendee INT AUTO_INCREMENT PRIMARY KEY,
  id_detail INT NOT NULL,
  kode_tiket VARCHAR(50) NOT NULL UNIQUE,
  status_checkin ENUM('belum','sudah') NOT NULL DEFAULT 'belum',
  waktu_checkin DATETIME DEFAULT NULL,
  FOREIGN KEY (id_detail) REFERENCES order_detail(id_detail)
);
```

---

### 📜 Script SQL Lengkap (Urutan Pembuatan)

> ⚠️ Tabel harus dibuat sesuai urutan berikut agar Foreign Key tidak error:

```sql
-- 1. Database
CREATE DATABASE event_tiket;
USE event_tiket;

-- 2. Tabel tanpa dependensi
CREATE TABLE users ( ... );
CREATE TABLE venue ( ... );
CREATE TABLE voucher ( ... );

-- 3. Tabel dengan 1 FK
CREATE TABLE event ( ... );  -- FK: venue

-- 4. Tabel dengan 2 FK
CREATE TABLE tiket ( ... );   -- FK: event
CREATE TABLE orders ( ... );  -- FK: users, voucher

-- 5. Tabel dengan 2 FK dari tabel sebelumnya
CREATE TABLE order_detail ( ... );  -- FK: orders, tiket

-- 6. Tabel terakhir
CREATE TABLE attendee ( ... );  -- FK: order_detail
```

---

## 🔹 BAGIAN B: Sistem Login

### Tugas 4 — Buat Sistem Login
Buat sistem login dengan ketentuan:
- **Input:** Email & Password
- **Role:** `admin` dan `user`
- **Redirect:**
  - `admin` → Dashboard Admin
  - `user` → Dashboard User

### Tugas 5 — Fitur Logout
Buat fitur logout menggunakan **session** PHP.

📌 **Output:**
- Halaman login berfungsi
- Session login aktif setelah masuk

---

## 🔹 BAGIAN C: CRUD Master Data (Admin)

### Tugas 6 — CRUD Venue
Kelola data venue dengan fitur:
- ➕ Tambah
- ✏️ Edit
- 🗑️ Hapus
- 📋 Tampil Data

### Tugas 7 — CRUD Event
Kelola data event dengan ketentuan:
- Relasi dengan tabel `venue`
- Input tanggal event

### Tugas 8 — CRUD Tiket
Kelola data tiket dengan ketentuan:
- Relasi ke tabel `event`
- Input harga & kuota tiket

### Tugas 9 — CRUD Voucher
Kelola data voucher dengan ketentuan:
- Kode voucher unik
- Potongan harga (nominal/persentase)
- Status: **aktif / nonaktif**

📌 **Output:** Halaman admin dengan fitur CRUD lengkap untuk semua entitas

---

## 🔹 BAGIAN D: Pemesanan Tiket (User)

### Tugas 10 — Halaman Katalog Event
Buat halaman:
- Daftar semua event yang tersedia
- Detail tiket per event

### Tugas 11 — Fitur Pemesanan
Buat form pemesanan dengan fitur:
- Pilih tiket
- Input jumlah (`qty`)
- Hitung subtotal otomatis

### Tugas 12 — Simpan Transaksi
Simpan data pemesanan ke:
- Tabel `orders`
- Tabel `order_detail`

📌 **Output:** Data transaksi tersimpan di database

---

## 🔹 BAGIAN E: Voucher & Pembayaran

### Tugas 13 — Fitur Voucher
Tambahkan fitur:
- Input kode voucher
- Validasi voucher (status aktif & masih tersedia)
- Hitung diskon secara otomatis

### Tugas 14 — Update Status Pembayaran
Update data order dengan:
- Total pembayaran setelah diskon
- Status order: `pending` atau `paid`

📌 **Output:** Perhitungan total dengan voucher berjalan dengan benar

---

## 🔹 BAGIAN F: Generate Tiket (Attendee)

### Tugas 15 — Generate Kode Tiket
Setelah order dibuat:
- Generate **kode tiket unik** untuk setiap peserta
- Simpan ke tabel `attendee`

### Tugas 16 — Jumlah Tiket Sesuai Qty
Jumlah tiket yang digenerate harus sesuai dengan jumlah qty yang dibeli.

📌 **Output:** Setiap pembelian menghasilkan kode tiket yang unik dan valid

---

## 🔹 BAGIAN G: Check-in Tiket

### Tugas 17 — Halaman Check-in
Buat halaman check-in dengan:
- Input kode tiket

### Tugas 18 — Proses Check-in
Jika kode tiket valid:
- Update `status_checkin = "sudah"`
- Simpan `waktu_checkin` (timestamp)

📌 **Output:** Sistem check-in berjalan dan mencatat waktu masuk peserta

---

## 🔹 BAGIAN H: Dashboard & Laporan

### Tugas 19 — Dashboard Admin
Buat dashboard yang menampilkan statistik:
- Total user terdaftar
- Total order masuk
- Total pendapatan

### Tugas 20 — Laporan
Buat laporan yang memuat:
- Data transaksi (semua order)
- Data tiket terjual per event

📌 **Output:** Halaman dashboard dengan data statistik yang informatif

---

## 🔹 BAGIAN I: Tampilan UI

### Tugas 21 — Implementasi Bootstrap
Gunakan **Bootstrap** untuk tampilan:
- ✅ Responsif di semua perangkat
- 🃏 Layout **card** untuk menampilkan daftar event
- 📊 **Tabel** untuk data di halaman admin

📌 **Output:** Tampilan modern, rapi, dan responsif

---

## 🔹 BAGIAN J: Soal HOTS (Analisis & Pengembangan)

### Tugas 22 — Analisis: Kuota Tiket
> Jelaskan cara mencegah pembelian tiket melebihi kuota yang tersedia.

**Panduan:** Gunakan pengecekan stok sebelum menyimpan order, serta pertimbangkan penggunaan transaksi database (`BEGIN TRANSACTION`) untuk menghindari *race condition*.

---

### Tugas 23 — Query: Total Tiket Terjual per Event
Buat query SQL yang menampilkan total tiket terjual untuk setiap event.

**Contoh struktur query:**
```sql
SELECT e.nama_event, SUM(od.qty) AS total_terjual
FROM event e
JOIN tiket t ON t.event_id = e.id
JOIN order_detail od ON od.tiket_id = t.id
JOIN orders o ON o.id = od.order_id
WHERE o.status = 'paid'
GROUP BY e.id;
```

---

### Tugas 24 — Fitur: Riwayat Pembelian User
Buat halaman yang menampilkan riwayat pembelian untuk user yang sedang login, mencakup:
- Daftar order
- Detail tiket yang dibeli
- Status pembayaran dan check-in

---

### Tugas 25 — Analisis: Voucher Tanpa Batas Kuota
> Analisis: Apa yang terjadi jika voucher tidak dibatasi kuota?

**Panduan:** Diskusikan potensi kerugian bisnis, penyalahgunaan sistem, dan solusi seperti menambahkan field `kuota` dan `sisa_kuota` pada tabel `voucher`.

---

## ⭐ BONUS (Nilai Tambahan)

Pilih **minimal 1** fitur bonus berikut untuk nilai tambahan:

| # | Fitur Bonus |
|---|---|
| 1 | Export laporan ke **PDF / Excel** |
| 2 | Tambahkan **grafik/chart** pada dashboard |
| 3 | **Upload gambar** event |
| 4 | Tambahkan **pagination & search** pada daftar data |

---

## 🎯 Output Akhir yang Diharapkan

Mahasiswa menghasilkan:

- ✅ **Aplikasi web** sistem pemesanan tiket event yang berfungsi penuh
- ✅ **Database** sesuai ERD dengan relasi yang benar
- ✅ **Sistem login** dengan role admin dan user
- ✅ **Sistem transaksi** pemesanan tiket berjalan
- ✅ **Generate tiket** & sistem check-in aktif
- ✅ **Dashboard** dengan data statistik

---

## 🛠️ Teknologi yang Digunakan

| Teknologi | Kegunaan |
|---|---|
| **PHP Native** | Backend / logika server |
| **MySQL** | Database relasional |
| **Bootstrap** | Framework CSS untuk tampilan UI |
| **SQL** | Query database |
| **Session PHP** | Manajemen login pengguna |

---

## 📁 Saran Struktur Folder Proyek

```
event-tiket/
├── index.php               # Halaman utama / redirect
├── login.php               # Halaman login
├── logout.php              # Proses logout
├── config/
│   └── db.php              # Koneksi database
├── admin/
│   ├── dashboard.php       # Dashboard admin
│   ├── venue/              # CRUD Venue
│   ├── event/              # CRUD Event
│   ├── tiket/              # CRUD Tiket
│   ├── voucher/            # CRUD Voucher
│   ├── checkin.php         # Halaman check-in
│   └── laporan.php         # Laporan transaksi
├── user/
│   ├── dashboard.php       # Dashboard user
│   ├── event-list.php      # Daftar event
│   ├── pesan.php           # Form pemesanan
│   └── riwayat.php         # Riwayat pembelian
└── assets/
    ├── css/                # File CSS tambahan
    ├── js/                 # File JavaScript
    └── img/                # Gambar/asset
```

---

> 📝 *README ini dibuat berdasarkan dokumen Soal Latihan UK — Sistem Informasi Pemesanan Tiket Event Berbasis Web.*