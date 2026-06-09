<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config.php';

if (!isset($config['session_name']) || !is_string($config['session_name'])) {
    throw new RuntimeException('Invalid session configuration.');
}

session_name($config['session_name']);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }

    return trim($value);
}

function text_limit(string $text, int $maxLen): string
{
    if ($maxLen < 1) {
        return '';
    }

    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $maxLen);
    }

    return substr($text, 0, $maxLen);
}
