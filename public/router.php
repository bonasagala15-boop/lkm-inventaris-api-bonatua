<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$file = __DIR__ . $path;

// File statis (mis. /openapi.yaml)
if ($path !== '/' && is_file($file)) {
    return false;
}

// Folder yang punya index.html (mis. /swagger/ atau /swagger)
$dir = rtrim($file, '/\\');
if ($path !== '/' && is_dir($dir) && is_file($dir . '/index.html')) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($dir . '/index.html');
    return true;
}

require __DIR__ . '/index.php';

