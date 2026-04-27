<nav class="pub-nav">
    <div class="container d-flex justify-content-between align-items-center px-4">
        <a href="index.php" class="navbar-brand d-flex align-items-center gap-2">
            <div style="width:32px; height:32px; background:var(--gradient-primary); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1rem; color:white;"><i class="ri-ticket-2-fill"></i></div>
            <span style="font-weight:800; background:var(--gradient-primary); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">E-Tiket</span>
        </a>
        <div class="d-flex gap-3 align-items-center">
            <a href="index.php" class="nav-link-pub <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Home</a>
            <a href="about.php" class="nav-link-pub <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">About</a>
            <a href="events.php" class="nav-link-pub <?= basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : '' ?>">Events</a>
            <button id="theme-toggle" class="btn btn-ghost btn-sm btn-icon" title="Toggle Theme">
                <i class="ri-moon-line" id="theme-icon"></i>
            </button>
            <?php if(!isset($_SESSION['login'])): ?>
                <a href="login.php" class="btn btn-primary btn-sm px-4">Login</a>
            <?php else: ?>
                <a href="user/dashboard.php" class="btn btn-ghost btn-sm px-4">Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    const themeBtn = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    
    function updateThemeIcon(theme) {
        themeIcon.className = theme === 'dark' ? 'ri-sun-line' : 'ri-moon-line';
    }

    // Init icon
    updateThemeIcon(document.documentElement.getAttribute('data-theme'));

    themeBtn.addEventListener('click', () => {
        let currentTheme = document.documentElement.getAttribute('data-theme');
        let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });
</script>
