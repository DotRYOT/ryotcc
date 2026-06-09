<?php

declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../lib/storage.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

require_auth_or_401();

$payloadJson = $_POST['order'] ?? '';
$payload = json_decode(is_string($payloadJson) ? $payloadJson : '', true);
if (!is_array($payload)) {
    json_response(['ok' => false, 'error' => 'Invalid order payload'], 422);
}

$dataFile = (string)$config['data_file'];
$statuses = ['todo', 'in_progress', 'done'];

$board = with_board_lock($dataFile, static function (array $board) use ($payload, $statuses): array {
    $board = clean_board_order($board);

    $nextOrder = ['todo' => [], 'in_progress' => [], 'done' => []];
    $seen = [];

    foreach ($statuses as $status) {
        $ids = $payload[$status] ?? [];
        if (!is_array($ids)) {
            continue;
        }

        foreach ($ids as $id) {
            if (!is_string($id) || isset($seen[$id]) || !isset($board['ideas'][$id])) {
                continue;
            }
            $seen[$id] = true;
            $board['ideas'][$id]['status'] = $status;
            $nextOrder[$status][] = $id;
        }
    }

    foreach ($board['ideas'] as $id => $idea) {
        if (isset($seen[$id])) {
            continue;
        }
        $status = normalized_status((string)($idea['status'] ?? 'todo'));
        $board['ideas'][$id]['status'] = $status;
        $nextOrder[$status][] = $id;
    }

    $board['order'] = $nextOrder;

    return $board;
});

json_response(['ok' => true, 'board' => clean_board_order($board)]);
