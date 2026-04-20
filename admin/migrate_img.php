<?php
require_once '../config/db.php';
$sql = "ALTER TABLE event ADD COLUMN gambar VARCHAR(255) DEFAULT 'default.jpg' AFTER deskripsi";
if (mysqli_query($conn, $sql)) {
    echo "Column 'gambar' added successfully.";
} else {
    echo "Error: " . mysqli_error($conn);
}
unlink(__FILE__);
