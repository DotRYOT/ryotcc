<?php

declare(strict_types=1);

function is_authenticated(): bool
{
    return !empty($_SESSION['idea_board_auth']);
}

function require_auth_or_401(): void
{
    if (!is_authenticated()) {
        json_response([
            'ok' => false,
            'error' => 'Unauthorized',
        ], 401);
    }
}

function login_with_otp(string $otp, string $expectedOtp): bool
{
    if ($otp === '' || $expectedOtp === '') {
        return false;
    }

    if (!hash_equals($expectedOtp, $otp)) {
        return false;
    }

    $_SESSION['idea_board_auth'] = true;
    $_SESSION['idea_board_auth_at'] = time();

    return true;
}

function logout_auth(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
