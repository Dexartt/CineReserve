<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineReserve - Premium Sinema Sistemi</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="overlay"></div>
    
    <!-- En üstteki giriş yap / kayıt ol barı -->
    <div class="auth-bar container">
        <div id="authGuest" class="auth-group">
            <button class="btn ghost-btn" onclick="openAuthModal('login')">Giriş Yap</button>
            <button class="btn primary-btn small" onclick="openAuthModal('register')">Kayıt Ol</button>
        </div>
        <div id="authUser" class="auth-group" style="display: none; align-items:center;">
            <a href="admin.php" id="adminLinkBtn" class="btn preview-btn" style="display:none; text-decoration:none; margin-right: 15px; background-color: #f39c12; color: #fff; padding: 0.3rem 0.8rem; border-radius: 5px; font-weight: bold;">👑 Yönetim Paneli</a>
            <span class="welcome-text">Hoş geldin, <strong id="loggedInUsername">Kullanıcı</strong></span>
            <button class="btn ghost-btn danger-text" onclick="handleLogout()">Çıkış Yap</button>
        </div>
    </div>

    <div class="container">
        
        <header>
            <div class="logo">
                <span class="icon">🎬</span> Cine<span>Reserve</span>
            </div>
            <p>Modern Sinema Rezervasyon Sistemi</p>
        </header>

        <main>
            <!-- Menü sekmeleri (Tablar) burada duruyor -->
            <div class="tabs-nav">
                <button class="tab-btn active" onclick="switchTab('tab-showcase')">Vizyondaki Filmler (Fragman & Bilgi)</button>
                <button class="tab-btn" onclick="switchTab('tab-booking')">Bilet Alım & Koltuk Seçimi</button>
                <button class="tab-btn" onclick="switchTab('tab-admin')">Film Talep Et</button>
            </div>

            <!-- 1. Sekme: Fragmanları ve film özetlerini gösterdiğim yer -->
            <section id="tab-showcase" class="tab-content active movies-container" style="margin-top: 2rem;">
                <h2 class="section-title">Film Bilgileri ve Fragmanlar</h2>
                <div id="showcaseList" class="showcase-grid"></div>
            </section>

            <!-- 2. Sekme: Koltuk seçip bilet satın alınan bölüm -->
            <section id="tab-booking" class="tab-content movies-container" style="margin-top: 2rem;">
                <h2 class="section-title">Koltuk Seçimi ve Bilet Alış</h2>
                <div id="moviesList" class="movies-grid"></div>
            </section>

            <!-- 3. Sekme: Kullanıcının yeni film istediği form alanı -->
            <section id="tab-admin" class="tab-content forms-panel glass-panel" style="margin-top: 2rem;">
                <div class="form-container">
                    <h2><span class="icon">💬</span> Görmek İstediğiniz Filmi Söyleyin</h2>
                    <form id="requestMovieForm">
                        <div class="input-group">
                            <label>Hangi filmi sinemamızda görmek istersiniz?</label>
                            <input type="text" id="requestMovieName" placeholder="Örn: The Dark Knight" required>
                        </div>
                        <button type="submit" class="btn primary-btn">Yönetime İlet</button>
                    </form>
                </div>
            </section>

        </main>
        
        <!-- Giriş veya kayıt ol denildiğinde ekrana açılan pencere (Modal) -->
        <div id="authModal" class="modal">
            <div class="modal-content glass-panel" style="max-width: 400px;">
                <span class="close-btn" onclick="closeAuthModal()">&times;</span>
                <h2 id="authModalTitle">Giriş Yap</h2>
                
                <form id="authForm">
                    <div class="input-group">
                        <label>Kullanıcı Adı</label>
                        <input type="text" id="authUsername" placeholder="Kullanıcı adınız..." required>
                    </div>
                    <div class="input-group" style="margin-top:1rem;">
                        <label>Şifre</label>
                        <input type="password" id="authPassword" placeholder="Şifreniz..." required>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="submit" id="authSubmitBtn" class="btn primary-btn w-100">Giriş</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Koltuğa tıklayınca bilet almak için açılan pencere -->
        <div id="bookingModal" class="modal">
            <div class="modal-content glass-panel">
                <span class="close-btn" onclick="closeModal()">&times;</span>
                <h2 id="modalTitle">Film Adı</h2>
                
                <div class="seat-info">
                    <p>Seçilen Koltuk: <span id="modalSeatNumber" class="highlight"></span></p>
                    <p id="modalStatus" class="status-badge"></p>
                </div>

                <form id="actionForm">
                    <div class="input-group" id="ticketTypeGroup">
                        <label>Bilet Türü Seçiniz</label>
                        <select id="ticketType">
                            <option value="Tam">Tam Bilet (150₺)</option>
                            <option value="Öğrenci">Öğrenci Bileti (100₺)</option>
                            <option value="Çocuk">Çocuk / 65+ Yaş (80₺)</option>
                        </select>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" id="btnBook" class="btn success-btn">Bilet Al</button>
                        <button type="button" id="btnCancel" class="btn danger-btn">İptal Et</button>
                    </div>
                </form>
            </div>
        </div>
        
    </div>

    <!-- Sağ altta yeşil/kırmızı çıkan bildirim mesajları (Toast) -->
    <div id="toast" class="toast">Mesaj</div>

    <script src="../../assets/js/app.js"></script>
</body>
</html>
