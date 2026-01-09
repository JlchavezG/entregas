    <?php
function sendWebNotification($title, $body, $tag = 'delivery') {
    $file = __DIR__ . '/../notifications.json';
    $notifications = [];
    if (file_exists($file)) {
        $notifications = json_decode(file_get_contents($file), true) ?: [];
    }
    $notifications[] = [
        'id' => uniqid(),
        'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
        'body' => htmlspecialchars($body, ENT_QUOTES, 'UTF-8'),
        'tag' => $tag,
        'timestamp' => time()
    ];
    file_put_contents($file, json_encode($notifications, JSON_PRETTY_PRINT));
}
?>