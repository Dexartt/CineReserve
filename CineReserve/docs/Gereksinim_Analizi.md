# Sinema Rezervasyon Sistemi Gereksinim Analizi

## 1. Proje Amacı
CineReserve sistemi, kullanıcıların vizyondaki filmleri inceleyebileceği, fragmanlarını izleyebileceği ve istedikleri filmin seansına koltuk seçerek bilet alabileceği modern bir web tabanlı otomasyon sistemidir.

## 2. İşlevsel Gereksinimler (Functional Requirements)
- **Kullanıcı İşlemleri:** Sisteme kayıt olma (Register), giriş yapma (Login) ve çıkış yapma işlemleri.
- **Film Görüntüleme:** Kullanıcılar ana sayfada ekli filmleri ve bunlara ait kapasite, açıklama, fragman bilgilerini görüntüleyebilir.
- **Bilet Rezervasyonu:** Kullanıcılar boş koltuklardan (Tam, Öğrenci, Çocuk tarifesine göre) seçim yaparak rezervasyon oluşturabilir.
- **Rezervasyon İptali:** Kullanıcı kendine ait olan rezervasyonu iptal edebilir.

## 3. İşlevsel Olmayan Gereksinimler (Non-Functional Requirements)
- **Performans:** Sistem eşzamanlı olarak çok sayıda API isteğini gecikmesiz karşılayabilmelidir.
- **Güvenlik:** Kullanıcı şifreleri sunucuda güvenli şekilde kontrol edilmeli ve yetkisiz kişilerin başkasının biletini iptal etmesi engellenmelidir. (Encapsulation kullanılmıştır).
- **Modülerlik:** Kaynak kod, Nesne Yönelimli Programlama mantığına uygun (Class, Inheritance, Polymorphism) sınıflara ayrıştırılmalıdır. MVC yaklaşımına uygun klasör hiyerarşisi benimsenmiştir.
