<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Simple health check
echo json_encode([
    'status' => 'ok',
    'message' => 'API is running',
    'timestamp' => time(),
    'version' => '1.0.0'
]);
