<?php

declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../lib/storage.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

require_auth_or_401();

$text = post_string('text');
if ($text === '') {
    json_response(['ok' => false, 'error' => 'Idea text is required'], 422);
}

$status = normalized_status(post_string('status'));
$dataFile = (string)$config['data_file'];

$board = with_board_lock($dataFile, static function (array $board) use ($text, $status): array {
    $board = clean_board_order($board);

    $id = uniqid('idea_', true);
    $board['ideas'][$id] = [
        'id' => $id,
        'text' => text_limit($text, 280),
        'status' => $status,
        'notes' => [],
        'created_at' => gmdate('c'),
    ];

    $board['order'][$status][] = $id;

    return $board;
});

json_response(['ok' => true, 'board' => clean_board_order($board)]);
