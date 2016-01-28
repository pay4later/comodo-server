<?php

header('Content-Type: application/json');

try {
    $container = require __DIR__ . '/../src/bootstrap.php';
    $controller = $container->get('controller');
    $result = $controller->dispatch();
} catch (Exception $e) {
    trigger_error($e->getMessage(), E_USER_WARNING);
    $result = ['error' => '500 Internal Server Error'];
}

echo json_encode($result, JSON_PRETTY_PRINT) . "\n";