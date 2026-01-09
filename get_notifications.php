<?php
header('Content-Type: application/json');
$file = __DIR__ . '/notifications.json';
$notifications = [];
if (file_exists($file)) {
    $content = file_get_contents($file);
    if ($content) $notifications = json_decode($content, true) ?: [];
    file_put_contents($file, '[]');
}
echo json_encode($notifications);
?>