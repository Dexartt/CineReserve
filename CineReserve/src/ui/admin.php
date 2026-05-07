<?php
session_start();

// Admin şifresini buradan belirleyebilirsiniz.
$ADMIN_PASSWORD = "enes123";

// Çıkış yapma işlemi
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

$error_msg = "";
// Giriş denemesi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_pass'])) {
    if ($_POST['admin_pass'] === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error_msg = "Hatalı şifre!";
    }
}

$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli - CineReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .admin-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        .admin-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .admin-list {
            list-style: none;
            padding: 0;
            max-height: 250px;
            overflow-y: auto;
        }
        .admin-list li {
            background: rgba(0,0,0,0.3);
            margin-bottom: 0.5rem;
            padding: 1rem;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-list.requests li {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .req-date {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        .delete-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .delete-btn:hover {
            background: #c0392b;
        }
        .login-container {
            max-width: 400px;
            margin: 4rem auto;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <div class="container">
        <header>
            <div class="logo">
                <span class="icon">👑</span> Cine<span>Admin</span>
            </div>
            <p>Sistem Yönetim Paneli</p>
            <div style="margin-top:1rem; display:flex; justify-content:center; gap:1rem;">
                <a href="index.php" class="btn ghost-btn">Ana Siteye Dön</a>
                <?php if($is_logged_in): ?>
                    <a href="admin.php?logout=1" class="btn ghost-btn danger-text">Güvenli Çıkış</a>
                <?php endif; ?>
            </div>
        </header>

        <main>
            <?php if(!$is_logged_in): ?>
                
                <!-- LOGIN FORM -->
                <div class="admin-card login-container">
                    <h2>Yönetici Girişi</h2>
                    <?php if($error_msg): ?>
                        <p style="color:#e74c3c; margin-bottom:1rem;"><?php echo $error_msg; ?></p>
                    <?php endif; ?>
                    <form method="POST" action="admin.php">
                        <div class="input-group" style="text-align:left;">
                            <label>Erişim Şifresi</label>
                            <input type="password" name="admin_pass" placeholder="Şifreyi giriniz..." required>
                        </div>
                        <button type="submit" class="btn primary-btn" style="margin-top: 1.5rem; width:100%;">Panele Gir</button>
                    </form>
                </div>

            <?php else: ?>

                <!-- ADMIN DASHBOARD -->
                <div class="admin-grid">
                    
                    <!-- MOVIE ADD FORM -->
                    <div class="admin-card">
                        <h2><span class="icon">➕</span> Yeni Film Ekle</h2>
                        <form id="addMovieForm">
                            <div class="input-group">
                                <label>Film Adı</label>
                                <input type="text" id="newMovieName" placeholder="Örn: Inception" required>
                            </div>
                            <div class="input-group" style="margin-top: 1rem;">
                                <label>Koltuk Sayısı</label>
                                <input type="number" id="newMovieSeats" min="1" placeholder="Örn: 100" required>
                            </div>
                            <div class="input-group" style="margin-top: 1rem;">
                                <label>Film Özeti</label>
                                <input type="text" id="newMovieDesc" placeholder="Kısa bir açıklama..." required>
                            </div>
                            <div class="input-group" style="margin-top: 1rem;">
                                <label>YouTube Fragman Linki</label>
                                <input type="url" id="newMovieTrailer" placeholder="https://youtube.com/watch?v=..." required>
                            </div>
                            <button type="submit" class="btn primary-btn" style="margin-top: 1rem;">Sisteme Ekle</button>
                        </form>
                    </div>

                    <!-- MOVIE LIST & DELETE -->
                    <div class="admin-card">
                        <h2><span class="icon">🎬</span> Aktif Filmler & Koltuk Durumu</h2>
                        <ul class="admin-list" id="adminMovieList">
                            <!-- Loaded dynamically -->
                        </ul>
                    </div>

                    <!-- MOVIE REQUESTS -->
                    <div class="admin-card">
                        <h2><span class="icon">📨</span> Gelen Film Talepleri</h2>
                        <ul class="admin-list requests" id="adminRequestsList">
                            <!-- Loaded dynamically -->
                        </ul>
                    </div>

                    <!-- USERS LIST -->
                    <div class="admin-card">
                        <h2><span class="icon">👥</span> Kayıtlı Üyeler</h2>
                        <ul class="admin-list" id="adminUsersList">
                            <!-- Loaded dynamically -->
                        </ul>
                    </div>

                </div>

            <?php endif; ?>
        </main>

    </div>

    <div id="toast" class="toast">Mesaj</div>

    <!-- Script only active if logged in, but we can safely load it because the elements won't exist if not logged in -->
    <?php if($is_logged_in): ?>
        <script src="../../assets/js/admin.js"></script>
    <?php endif; ?>
</body>
</html>
