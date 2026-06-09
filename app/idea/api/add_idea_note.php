<?php

declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../lib/storage.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

require_auth_or_401();

$ideaId = post_string('idea_id');
$text = post_string('text');

if ($ideaId === '' || $text === '') {
    json_response(['ok' => false, 'error' => 'Idea and text are required'], 422);
}

$dataFile = (string)$config['data_file'];

try {
    $board = with_board_lock($dataFile, static function (array $board) use ($ideaId, $text): array {
        $board = clean_board_order($board);

        if (!isset($board['ideas'][$ideaId])) {
            throw new RuntimeException('Idea not found');
        }

        if (!isset($board['ideas'][$ideaId]['notes']) || !is_array($board['ideas'][$ideaId]['notes'])) {
            $board['ideas'][$ideaId]['notes'] = [];
        }

        array_unshift($board['ideas'][$ideaId]['notes'], [
            'id' => uniqid('inote_', true),
            'text' => text_limit($text, 220),
            'created_at' => gmdate('c'),
        ]);

        $board['ideas'][$ideaId]['notes'] = array_slice($board['ideas'][$ideaId]['notes'], 0, 50);

        return $board;
    });
} catch (RuntimeException $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 404);
}

json_response(['ok' => true, 'board' => clean_board_order($board)]);
