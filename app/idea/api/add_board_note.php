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
    json_response(['ok' => false, 'error' => 'Note text is required'], 422);
}

$dataFile = (string)$config['data_file'];
$board = with_board_lock($dataFile, static function (array $board) use ($text): array {
    $board = clean_board_order($board);

    array_unshift($board['board_notes'], [
        'id' => uniqid('note_', true),
        'text' => text_limit($text, 300),
        'created_at' => gmdate('c'),
    ]);

    $board['board_notes'] = array_slice($board['board_notes'], 0, 200);

    return $board;
});

json_response(['ok' => true, 'board' => clean_board_order($board)]);
