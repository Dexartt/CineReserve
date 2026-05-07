# CineReserve (Sinema Rezervasyon Sistemi)

## Proje Adı
CineReserve

## Proje Amacı
Bu proje, modern ve duyarlı (responsive) bir arayüze sahip bir sinema bilet otomasyonu oluşturmayı amaçlamaktadır. Proje, tamamen PHP, HTML, CSS ve JavaScript kullanılarak geliştirilmiştir. Kullanıcılar sisteme üye olabilir, vizyondaki filmleri ve fragmanları görüntüleyebilir ve istedikleri filme koltuk bazlı (Tam, Öğrenci, Çocuk tarifeli) rezervasyon yapabilirler. Sistem mimarisi, verilen klasör yapısı kurallarına sadık kalınarak tasarlanmıştır.

## Kurulum ve Çalıştırma Talimatı (XAMPP ile)
Bu proje, XAMPP yerel sunucusu ve MySQL veritabanı kullanılarak çalışacak şekilde yapılandırılmıştır.

1. **XAMPP'ı Başlatın:** XAMPP Control Panel'i açın ve hem **Apache** hem de **MySQL** modüllerini başlatın (Start).
2. **Proje Konumu:** Proje klasörünün (`CineReserve`), `C:\xampp\htdocs\` klasörünün içinde olduğundan emin olun (Örn: `C:\xampp\htdocs\CineReserve`).
3. **Veritabanı Kurulumu:**
   - Tarayıcınızda `http://localhost/phpmyadmin` adresine gidin.
   - Sol menüden `cinema_db` adında yeni bir veritabanı oluşturun (Karşılaştırma olarak `utf8mb4_unicode_ci` seçebilirsiniz).
   - Oluşturduğunuz veritabanını seçin, üst menüden "İçe Aktar" (Import) sekmesine tıklayın.
   - Proje ana dizinindeki `database.sql` dosyasını seçin ve içe aktarın.
4. **Siteye Erişim:**
   - Tarayıcınızı açın ve kullanıcı arayüzü için: `http://localhost/CineReserve/src/ui/index.php` adresine gidin.
   - Yönetim paneli için: `http://localhost/CineReserve/src/ui/admin.php` adresini kullanabilirsiniz.

**Not:** Varsayılan admin hesabı için:
- Kullanıcı adı: `enes12`
- Şifre: `enes12`

Yeni admin yetkisi vermek isterseniz, phpMyAdmin üzerinden `users` tablosunda ilgili kullanıcının `is_admin` değerini `1` yapabilirsiniz.
