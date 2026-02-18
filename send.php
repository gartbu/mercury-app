<?php
// send.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Только POST запросы']);
    exit;
}

// Получаем данные из формы
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$message = trim($_POST['message'] ?? '');
$district = trim($_POST['district'] ?? '');
$organization = trim($_POST['organization'] ?? '');

// Простая валидация
if (empty($name) || empty($phone) || empty($address)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Заполните обязательные поля']);
    exit;
}

// МАРШРУТИЗАЦИЯ: Email-адреса организаций (скрыты от клиента)
// Данные взяты из файла "Уборка улиц(адреса и телефоны).docx"
$recipients = [
    'dzerzhinsky' => 'uchastokd00@mail.ru',
    'kalininsky' => 'AHarutkin@admnsk.ru',
    'oktyabrsky' => 'dispetcherskaya.okt@rambler.ru',
    'pervomaysky' => 'mku.pervomayskoe@mail.ru', // Требуется уточнение
    'central' => 'mku.deu1@mail.ru',
    'kirovsky_main' => 'deu4@mail.ru', // Требуется уточнение
    'kirovsky_local' => 'arudnevmky@mail.ru',
    'leninsky_main' => 'deu3@mail.ru', // Требуется уточнение
    'leninsky_local' => 'Vyanushko@admnsk.ru',
    'sovetsky_right' => 'deu.sovetsky@mail.ru', // Требуется уточнение
    'sovetsky_left' => 'deu.sovetsky@mail.ru', // Требуется уточнение
    'sovetsky_local' => 'OAFomina@admnsk.ru'
];

// Определяем получателя
$to = $recipients[$district] ?? 'edds_default@mail.ru'; // Резервный email

// Формируем тему письма
$subject = "🧹 Жалоба на уборку: {$district} ({$organization})";

// Формируем тело письма
$body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { background: #007AFF; color: white; padding: 15px; border-radius: 5px; }
        .content { padding: 15px; background: #f9f9f9; }
        .label { font-weight: bold; color: #555; }
    </style>
</head>
<body>
    <div class='header'>
        <h2>Новая жалоба с портала ЕДДС</h2>
    </div>
    <div class='content'>
        <p><span class='label'>📍 Район:</span> {$district}</p>
        <p><span class='label'>🏢 Организация:</span> {$organization}</p>
        <hr>
        <p><span class='label'>👤 Заявитель:</span> {$name}</p>
        <p><span class='label'>📞 Телефон:</span> {$phone}</p>
        <p><span class='label'>🏠 Адрес проблемы:</span> {$address}</p>
        <p><span class='label'>📝 Описание:</span><br>{$message}</p>
        <hr>
        <p style='font-size: 12px; color: #999;'>Отправлено: " . date('d.m.Y H:i') . "</p>
    </div>
</body>
</html>
";

// Заголовки для корректной отправки HTML и кодировки
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: ЕДДС Сервис <no-reply@yourdomain.com>" . "\r\n"; // Замените на ваш домен
$headers .= "Reply-To: {$phone}" . "\r\n";

// Отправка письма
if (mail($to, $subject, $body, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Письмо отправлено']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка отправки почты']);
}
?>
