CREATE DATABASE IF NOT EXISTS cinema_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cinema_db;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `is_admin` BOOLEAN DEFAULT FALSE
);

CREATE TABLE `movies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) UNIQUE NOT NULL,
    `total_seats` INT NOT NULL,
    `available_seats` INT NOT NULL,
    `description` TEXT,
    `trailer_url` VARCHAR(255)
);

CREATE TABLE `reservations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `movie_id` INT NOT NULL,
    `seat_number` INT NOT NULL,
    `user_id` INT NOT NULL,
    `ticket_type` VARCHAR(50) DEFAULT 'Tam',
    FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_seat_movie` (`movie_id`, `seat_number`)
);

CREATE TABLE `requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `movie_name` VARCHAR(255) NOT NULL,
    `user_id` INT NOT NULL,
    `request_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Örnek Verilerin Eklenmesi (database.json baz alınarak)

INSERT INTO `users` (`id`, `username`, `password`, `is_admin`) VALUES
(1, 'enes12', 'enes12', TRUE),
(2, 'mehmet13', 'mehmet13', TRUE),
(3, 'ahmet14', 'ahmet14', FALSE);

INSERT INTO `movies` (`id`, `name`, `total_seats`, `available_seats`, `description`, `trailer_url`) VALUES
(1, 'The Matrix', 50, 50, 'Bir bilgisayar korsanı olan Neo, gizemli isyancı Morpheus ile tanıştığında, dünyasının aslında bir bilgisayar programı olduğunu keşfeder. Gerçeği öğrendikten sonra insanlığın özgürlüğü için savaşmaya başlar.', 'https://www.youtube.com/embed/vKQi3bBA1y8'),
(2, 'Inception', 100, 100, 'Rüya hırsızı Cobb, insanların bilinçaltından sırları çalmaktadır. Ona hayatını geri verebilecek son bir görev teklif edilir: Ancak bu sefer fikir çalmak değil, bir fikri bir zihne yerleştirmesi gerekmektedir.', 'https://www.youtube.com/embed/YoHD9XEInc0');

-- Kullanıcıların film talepleri

INSERT INTO `requests` (`movie_name`, `user_id`, `request_date`) VALUES
('Jujutsu Kaisen', 1, '2026-04-12 00:54:37'),
('Jujutsu Kaisen', 1, '2026-04-12 01:05:51'),
('Jujutsu Kaisen', 1, '2026-04-12 01:05:53'),
('Jujutsu Kaisen', 2, '2026-04-12 01:06:03'),
('Jujutsu Kaisen', 2, '2026-04-12 01:06:05'),
('Transformers Karanlık', 3, '2026-04-12 01:06:37'),
('Transformers Karanlık', 3, '2026-04-12 01:06:38'),
('Transformers Karanlık', 3, '2026-04-12 01:06:40'),
('Transformers Karanlık', 3, '2026-04-12 01:06:42'),
('Transformers Karanlık', 2, '2026-04-12 01:06:51');
