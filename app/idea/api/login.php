<?php

declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$otp = post_string('otp');
$expectedOtp = (string)($config['otp'] ?? '');

if (!login_with_otp($otp, $expectedOtp)) {
    json_response(['ok' => false, 'error' => 'Invalid code'], 401);
}

json_response(['ok' => true]);
