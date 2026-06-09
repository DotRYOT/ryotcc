<?php

declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../lib/storage.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

require_auth_or_401();

$noteId = post_string('note_id');
if ($noteId === '') {
    json_response(['ok' => false, 'error' => 'Note id is required'], 422);
}

$dataFile = (string)$config['data_file'];
$board = with_board_lock($dataFile, static function (array $board) use ($noteId): array {
    $board = clean_board_order($board);

    $board['board_notes'] = array_values(array_filter(
        (array)($board['board_notes'] ?? []),
        static fn (array $note): bool => (string)($note['id'] ?? '') !== $noteId
    ));

    return $board;
});

json_response(['ok' => true, 'board' => clean_board_order($board)]);
