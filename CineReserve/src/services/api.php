<?php
session_start();
header('Content-Type: application/json');

$host = 'localhost';
$db   = 'cinema_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(["success" => false, "message" => "Veritabanı bağlantı hatası: " . $e->getMessage()]);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$requestMethod = $_SERVER['REQUEST_METHOD'];

// JavaScript'ten gelen verileri alıyorum (Genelde JSON olarak atıyorum)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);
if (!$input && $requestMethod == 'POST') {
    // Eğer JSON gelmezse normal form post edilmiştir diye buraya düşürüyorum
    $input = $_POST;
}

$response = ["success" => false, "message" => "Bilinmeyen eylem."];

switch ($action) {
    case 'login':
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $userObj = $stmt->fetch();
        
        if ($userObj && $userObj['password'] === $password) {
            $is_admin = (bool)$userObj['is_admin'];
            if ($is_admin) {
                $_SESSION['admin_logged_in'] = true;
            }
            $response = ["success" => true, "message" => "Giriş başarılı.", "username" => $userObj['username'], "is_admin" => $is_admin];
        } else {
            $response = ["success" => false, "message" => "Kullanıcı adı veya şifre hatalı."];
        }
        break;

    case 'logout':
        session_destroy();
        $response = ["success" => true, "message" => "Çıkış yapıldı."];
        break;

    case 'register':
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $response = ["success" => false, "message" => "Kullanıcı adı ve şifre boş olamaz."];
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $response = ["success" => false, "message" => "Bu kullanıcı adı zaten alınmış."];
            } else {
                $stmt = $pdo->prepare('INSERT INTO users (username, password, is_admin) VALUES (?, ?, 0)');
                $stmt->execute([$username, $password]);
                $response = ["success" => true, "message" => "Kayıt başarılı. Hoş geldin $username!"];
            }
        }
        break;

    case 'movies':
        if ($requestMethod === 'GET') {
            $stmt = $pdo->query('SELECT * FROM movies');
            $movies = $stmt->fetchAll();
            $movieList = [];
            foreach ($movies as $movie) {
                $resStmt = $pdo->prepare('
                    SELECT r.seat_number, u.username, r.ticket_type 
                    FROM reservations r 
                    JOIN users u ON r.user_id = u.id 
                    WHERE r.movie_id = ?
                ');
                $resStmt->execute([$movie['id']]);
                $reservations = [];
                while ($res = $resStmt->fetch()) {
                    $reservations[$res['seat_number']] = [
                        "user_name" => $res['username'],
                        "ticket_type" => $res['ticket_type']
                    ];
                }
                
                $movieList[] = [
                    "name" => $movie['name'],
                    "total_seats" => (int)$movie['total_seats'],
                    "available_seats" => (int)$movie['available_seats'],
                    "description" => $movie['description'],
                    "trailer_url" => $movie['trailer_url'],
                    "reservations" => $reservations
                ];
            }
            $response = ["success" => true, "data" => $movieList];
        } elseif ($requestMethod === 'POST') {
            $name = $input['name'] ?? '';
            $seats = (int)($input['total_seats'] ?? 0);
            $desc = $input['description'] ?? '';
            $trailer = $input['trailer_url'] ?? '';

            if (empty($name) || $seats <= 0) {
                $response = ["success" => false, "message" => "Film adı ve geçerli koltuk sayısı zorunludur."];
            } else {
                $stmt = $pdo->prepare('SELECT id FROM movies WHERE name = ?');
                $stmt->execute([$name]);
                if ($stmt->fetch()) {
                    $response = ["success" => false, "message" => "'$name' filmi zaten sistemde mevcut."];
                } else {
                    if (strpos($trailer, 'watch?v=') !== false) {
                        $trailer = str_replace("watch?v=", "embed/", $trailer);
                    }
                    
                    $stmt = $pdo->prepare('INSERT INTO movies (name, total_seats, available_seats, description, trailer_url) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$name, $seats, $seats, $desc, $trailer]);
                    $response = ["success" => true, "message" => "'$name' filmi eklendi."];
                }
            }
        }
        break;

    case 'delete_movie':
        if ($requestMethod === 'POST') {
            $name = $input['name'] ?? '';
            $stmt = $pdo->prepare('SELECT id FROM movies WHERE name = ?');
            $stmt->execute([$name]);
            if ($movie = $stmt->fetch()) {
                $pdo->prepare('DELETE FROM movies WHERE id = ?')->execute([$movie['id']]);
                $response = ["success" => true, "message" => "'$name' filmi ve rezervasyonları silindi."];
            } else {
                $response = ["success" => false, "message" => "Film bulunamadı."];
            }
        }
        break;

    case 'book':
        $movie_name = $input['movie_name'] ?? '';
        $seat_number = (int)($input['seat_number'] ?? 0);
        $user_name = $input['user_name'] ?? '';
        $ticket_type = $input['ticket_type'] ?? 'Tam';

        $userStmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $userStmt->execute([$user_name]);
        $userObj = $userStmt->fetch();

        $movieStmt = $pdo->prepare('SELECT * FROM movies WHERE name = ?');
        $movieStmt->execute([$movie_name]);
        $movieObj = $movieStmt->fetch();

        if (!$userObj) {
            $response = ["success" => false, "message" => "Geçersiz kullanıcı veya giriş yapmadınız."];
        } elseif (!$movieObj) {
            $response = ["success" => false, "message" => "Film bulunamadı."];
        } elseif ($seat_number < 1 || $seat_number > $movieObj['total_seats']) {
            $response = ["success" => false, "message" => "Geçersiz koltuk numarası."];
        } else {
            $checkRes = $pdo->prepare('SELECT id FROM reservations WHERE movie_id = ? AND seat_number = ?');
            $checkRes->execute([$movieObj['id'], $seat_number]);
            if ($checkRes->fetch()) {
                $response = ["success" => false, "message" => "$seat_number numaralı koltuk şu anda dolu."];
            } else {
                $pdo->prepare('INSERT INTO reservations (movie_id, seat_number, user_id, ticket_type) VALUES (?, ?, ?, ?)')
                    ->execute([$movieObj['id'], $seat_number, $userObj['id'], $ticket_type]);
                $pdo->prepare('UPDATE movies SET available_seats = available_seats - 1 WHERE id = ?')
                    ->execute([$movieObj['id']]);
                $response = ["success" => true, "message" => "$seat_number numaralı koltuk başarıyla satın alındı."];
            }
        }
        break;

    case 'cancel':
        $movie_name = $input['movie_name'] ?? '';
        $seat_number = (int)($input['seat_number'] ?? 0);
        $user_name = $input['user_name'] ?? '';

        $userStmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $userStmt->execute([$user_name]);
        $userObj = $userStmt->fetch();

        $movieStmt = $pdo->prepare('SELECT id FROM movies WHERE name = ?');
        $movieStmt->execute([$movie_name]);
        $movieObj = $movieStmt->fetch();

        if (!$movieObj) {
            $response = ["success" => false, "message" => "Film bulunamadı."];
        } else {
            $resStmt = $pdo->prepare('SELECT user_id FROM reservations WHERE movie_id = ? AND seat_number = ?');
            $resStmt->execute([$movieObj['id'], $seat_number]);
            $res = $resStmt->fetch();
            
            if (!$res) {
                $response = ["success" => false, "message" => "Bu koltukta rezervasyon yok."];
            } elseif (!$userObj || $res['user_id'] != $userObj['id']) {
                $response = ["success" => false, "message" => "Bu bilet size ait değil, iptal edemezsiniz."];
            } else {
                $pdo->prepare('DELETE FROM reservations WHERE movie_id = ? AND seat_number = ?')
                    ->execute([$movieObj['id'], $seat_number]);
                $pdo->prepare('UPDATE movies SET available_seats = available_seats + 1 WHERE id = ?')
                    ->execute([$movieObj['id']]);
                $response = ["success" => true, "message" => "$seat_number numaralı bilet iptal edildi."];
            }
        }
        break;

    case 'request_movie':
        if ($requestMethod === 'POST') {
            $movie_name = $input['movie_name'] ?? '';
            $user_name = $input['user_name'] ?? '';
            
            if (empty($movie_name)) {
                $response = ["success" => false, "message" => "Film adı boş olamaz."];
            } else {
                $userStmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
                $userStmt->execute([$user_name]);
                if ($userObj = $userStmt->fetch()) {
                    $pdo->prepare('INSERT INTO requests (movie_name, user_id) VALUES (?, ?)')
                        ->execute([$movie_name, $userObj['id']]);
                    $response = ["success" => true, "message" => "Talebiniz yönetime iletildi."];
                } else {
                    $response = ["success" => false, "message" => "Geçersiz kullanıcı."];
                }
            }
        } elseif ($requestMethod === 'GET') {
            $stmt = $pdo->query('
                SELECT r.movie_name, u.username as user_name, r.request_date as date 
                FROM requests r 
                JOIN users u ON r.user_id = u.id
            ');
            $response = ["success" => true, "data" => $stmt->fetchAll()];
        }
        break;

    case 'users':
        if ($requestMethod === 'GET') {
            $stmt = $pdo->query('SELECT username, is_admin FROM users');
            $usersList = [];
            while ($row = $stmt->fetch()) {
                $usersList[] = [
                    "username" => $row['username'],
                    "is_admin" => (bool)$row['is_admin']
                ];
            }
            $response = ["success" => true, "data" => $usersList];
        }
        break;

    case 'toggle_admin':
        if ($requestMethod === 'POST') {
            $username = $input['username'] ?? '';
            $make_admin = $input['make_admin'] ?? false;
            
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $pdo->prepare('UPDATE users SET is_admin = ? WHERE username = ?')
                    ->execute([(int)$make_admin, $username]);
                $response = ["success" => true, "message" => "Yetkiler güncellendi."];
            } else {
                $response = ["success" => false, "message" => "Kullanıcı bulunamadı."];
            }
        }
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
