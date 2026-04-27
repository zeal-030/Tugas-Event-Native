<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<?php
// Deteksi kedalaman folder biar CSS gak pecah
$current_path = $_SERVER['PHP_SELF'];
$path_parts = explode('/', trim($current_path, '/'));
// Jika di localhost/event-ku/index.php -> parts: [event-ku, index.php] 
// Jika di localhost/event-ku/user/dashboard.php -> parts: [event-ku, user, dashboard.php]
// Kita hitung jumlah folder setelah nama project
$depth = count($path_parts) - 2; // -2 karena dikurangi nama project dan nama file
$prefix = str_repeat('../', max(0, $depth));
?>
<link href="<?= $prefix ?>assets/css/style.css?v=<?= time() ?>" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script>
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
</script>
