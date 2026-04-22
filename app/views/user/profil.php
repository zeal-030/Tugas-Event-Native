<?php
/**
 * View: User Profile Settings
 * Data dari UserDashboardController::profil(): $user, $msg
 */
$currentUser = currentUser();
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <title>Profil Saya — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .profile-wrapper { max-width: 1100px; margin: 0 auto; }
        
        /* Breadcrumb Fix */
        .breadcrumb-item a { color: var(--text-muted) !important; text-decoration: none; }
        .breadcrumb-item.active { color: var(--text-primary) !important; font-weight: 600; }
        .breadcrumb-item + .breadcrumb-item::before { color: var(--border) !important; }

        /* Profile Header Banner */
        .pr-banner {
            height: 180px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border-radius: 24px;
            position: relative;
            margin-bottom: -60px;
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.2);
        }
        
        .pr-card-main {
            background: var(--bg-surface);
            border-radius: 24px;
            border: 1px solid var(--border);
            padding: 2.5rem;
            position: relative;
            z-index: 2;
            box-shadow: var(--shadow-lg);
        }

        .pr-avatar-lg {
            width: 120px; height: 120px;
            background: var(--bg-base);
            border: 6px solid var(--bg-surface);
            border-radius: 35px;
            display: flex; align-items: center; justify-content: center;
            font-size: 3rem; font-weight: 800; color: var(--primary);
            margin-bottom: 1.5rem;
        }

        /* Stats Section */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-box {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.05);
            padding: 1.5rem; border-radius: 20px;
            transition: all 0.3s ease;
        }
        .stat-box:hover { background: var(--bg-hover); transform: translateY(-5px); }
        .text-muted-custom { color: var(--text-muted) !important; opacity: 0.8; }
        [data-theme="dark"] .text-muted-custom { color: #cbd5e1 !important; opacity: 0.6; }
        .stat-label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; font-weight: 700; }
        .stat-value { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); }
        .stat-icon { font-size: 1.5rem; color: var(--primary); margin-bottom: 1rem; opacity: 0.8; }

        /* Form Styling */
        .form-section-title { font-size: 1.1rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; }
        .form-label { font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.6rem; display: block; }
        .form-control {
            background: var(--bg-elevated); border: 1px solid var(--border); color: var(--text-primary);
            border-radius: 14px; padding: 0.85rem 1.2rem; font-size: 0.95rem;
        }
        .form-control:focus { border-color: var(--primary); background: var(--bg-elevated); color: var(--text-primary); box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1); }
        
        .btn-update {
            background: var(--gradient-primary); color: white; border: none;
            padding: 1rem 2.5rem; border-radius: 14px; font-weight: 700;
            display: flex; align-items: center; gap: 10px; transition: all 0.3s;
        }
        .btn-update:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4); }

        .badge-status { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        
        .profile-email { color: var(--text-secondary) !important; }
        .member-date { color: var(--text-primary) !important; }

    </style>
</head>
<body>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="main-content">
    <div class="topnav">
        <div class="topnav-left">
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/user/dashboard.php" class="text-decoration-none small">Dashboard</a></li>
                <li class="breadcrumb-item active small">Account Settings</li>
            </ol></nav>
        </div>
    </div>

    <div class="page-body" style="padding: 2rem;">
        <div class="profile-wrapper">
            <div class="pr-banner"></div>
            
            <div class="pr-card-main">
                <div class="row">
                    <div class="col-lg-4 text-center text-lg-start">
                        <div class="pr-avatar-lg mx-auto mx-lg-0"><?= strtoupper(substr($profile_user['nama'], 0, 1)) ?></div>
                        <h2 class="fw-800 mb-1" style="color: var(--text-primary);"><?= htmlspecialchars($profile_user['nama']) ?></h2>
                        <p class="profile-email small mb-4"><?= htmlspecialchars($profile_user['email']) ?> &bull; <span class="badge-status">Active Account</span></p>
                        
                        <div class="stat-box text-start mb-3">
                            <div class="stat-label text-muted-custom">Member Since</div>
                            <div class="stat-value member-date" style="font-size: 1.1rem;">April 2026</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-8">
                        <div class="stats-grid">
                            <div class="stat-box">
                                <i class="ri-ticket-2-line stat-icon"></i>
                                <div class="stat-label">Tickets Bought</div>
                                <div class="stat-value"><?= number_format($total_tickets) ?></div>
                            </div>
                            <div class="stat-box">
                                <i class="ri-wallet-3-line stat-icon"></i>
                                <div class="stat-label">Total Spent</div>
                                <div class="stat-value">Rp <?= number_format($total_spent, 0, ',', '.') ?></div>
                            </div>
                        </div>

                        <?php if ($msg === 'success'): ?>
                            <div class="alert alert-success d-flex align-items-center mb-4" style="border-radius: 16px; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #10b981;">
                                <i class="ri-checkbox-circle-line me-2 fs-5"></i> 
                                <span class="small fw-bold">Profile successfully updated!</span>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="form-section-title">
                                <i class="ri-user-settings-line"></i> Personal Information
                            </div>
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($profile_user['nama']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($profile_user['email']) ?>" required>
                                </div>
                            </div>

                            <div class="form-section-title">
                                <i class="ri-lock-password-line"></i> Security
                            </div>
                            <div class="row g-4 mb-5">
                                <div class="col-12">
                                    <label class="form-label">Change Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                                    <p class="text-muted mt-2 mb-0" style="font-size: 0.72rem; font-style: italic;">Note: Password must be at least 8 characters long for better security.</p>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn-update">
                                    <i class="ri-save-3-line"></i> Update Profile Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
