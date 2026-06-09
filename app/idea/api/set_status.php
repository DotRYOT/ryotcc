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
$status = normalized_status(post_string('status'));

if ($ideaId === '') {
    json_response(['ok' => false, 'error' => 'Missing idea id'], 422);
}

$dataFile = (string)$config['data_file'];
try {
    $board = with_board_lock($dataFile, static function (array $board) use ($ideaId, $status): array {
        $board = clean_board_order($board);

        if (!isset($board['ideas'][$ideaId])) {
            throw new RuntimeException('Idea not found');
        }

        $oldStatus = normalized_status((string)($board['ideas'][$ideaId]['status'] ?? 'todo'));
        $board['ideas'][$ideaId]['status'] = $status;

        $board['order'][$oldStatus] = array_values(array_filter($board['order'][$oldStatus], static fn ($id) => $id !== $ideaId));
        if (!in_array($ideaId, $board['order'][$status], true)) {
            array_unshift($board['order'][$status], $ideaId);
        }

        return $board;
    });
} catch (RuntimeException $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 404);
}

json_response(['ok' => true, 'board' => clean_board_order($board)]);
