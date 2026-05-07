# UML Diyagramları

Aşağıdaki diyagramlar projenin çalışma prensibini ve nesneye dayalı mimarisini (OOP) açıklamaktadır. Diyagramlar Mermaid formatında oluşturulmuştur.

## 1. Class Diagram (Sınıf Diyagramı)

Bu diyagram sistemdeki sınıfları, özelliklerini (encapsulation) ve aralarındaki kalıtım (inheritance) ilişkilerini göstermektedir.

```mermaid
classDiagram
    class User {
        -_username: string
        -_password: string
        +get_username(): string
        +check_password(password: string): bool
    }

    class Movie {
        -_name: string
        -_total_seats: int
        -_available_seats: int
        -_description: string
        -_trailer_url: string
        -_reservations: dict
        +get_name(): string
        +get_available_seats(): int
        +book_seat(seat_number, ticket: Ticket): bool
        +cancel_seat(seat_number, username): bool
    }

    class Ticket {
        <<abstract>>
        -_seat_number: int
        -_user_name: string
        +get_price()*: float
        +get_type(): string
        +get_seat(): int
        +get_user(): string
    }

    class StandardTicket {
        +get_price(): float
        +get_type(): string
    }

    class StudentTicket {
        +get_price(): float
        +get_type(): string
    }

    class ChildTicket {
        +get_price(): float
        +get_type(): string
    }

    class Cinema {
        -_movies: dict
        -_users: dict
        +register_user()
        +login_user()
        +add_movie()
        +book_ticket()
        +cancel_reservation()
    }

    Ticket <|-- StandardTicket : Kalıtım (Inheritance)
    Ticket <|-- StudentTicket : Kalıtım (Inheritance)
    Ticket <|-- ChildTicket : Kalıtım (Inheritance)

    Cinema *-- User : İçerir (Composition)
    Cinema *-- Movie : İçerir (Composition)
    Movie o-- Ticket : Rezerve Eder (Aggregation)
```

## 2. Use Case Diyagramı (Kullanım Durumu)

Sistemi kullanan aktörlerin gerçekleştirebileceği eylemleri gösterir.

```mermaid
usecaseDiagram
actor Kullanici as "Kayıtlı Kullanıcı"
actor Ziyaretci as "Ziyaretçi"

rectangle "Sinema Rezervasyon Sistemi" {
  Ziyaretci --> (Kayıt Ol)
  Ziyaretci --> (Giriş Yap)
  Ziyaretci --> (Filmleri ve Fragmanları Görüntüle)

  (Kayıt Ol) .> (Giriş Yap) : include
  
  Kullanici --> (Filmleri ve Fragmanları Görüntüle)
  Kullanici --> (Bilet Rezerve Et)
  Kullanici --> (Rezervasyon İptal Et)

  (Bilet Rezerve Et) .> (Giriş Yap) : include
}
```
