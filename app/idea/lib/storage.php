<?php

declare(strict_types=1);

function default_board(): array
{
    return [
        'version' => 1,
        'updated_at' => gmdate('c'),
        'ideas' => [],
        'order' => [
            'todo' => [],
            'in_progress' => [],
            'done' => [],
        ],
        'board_notes' => [],
    ];
}

function ensure_data_file(string $dataFile): void
{
    $dir = dirname($dataFile);
    if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
        throw new RuntimeException('Unable to create data directory.');
    }

    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, json_encode(default_board(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

function with_board_lock(string $dataFile, callable $callback): array
{
    ensure_data_file($dataFile);

    $handle = fopen($dataFile, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Unable to open data file.');
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Unable to lock data file.');
        }

        $raw = stream_get_contents($handle);
        $board = json_decode($raw ?: '', true);
        if (!is_array($board)) {
            $board = default_board();
        }

        $result = $callback($board);
        if (!is_array($result)) {
            throw new RuntimeException('Invalid board update result.');
        }

        $result['version'] = (int)($board['version'] ?? 1) + 1;
        $result['updated_at'] = gmdate('c');

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        fflush($handle);

        flock($handle, LOCK_UN);
        fclose($handle);

        return $result;
    } catch (Throwable $e) {
        flock($handle, LOCK_UN);
        fclose($handle);
        throw $e;
    }
}

function read_board(string $dataFile): array
{
    ensure_data_file($dataFile);
    $raw = file_get_contents($dataFile);
    $board = json_decode($raw ?: '', true);
    if (!is_array($board)) {
        return default_board();
    }

    $board['ideas'] = is_array($board['ideas'] ?? null) ? $board['ideas'] : [];
    $board['order'] = is_array($board['order'] ?? null) ? $board['order'] : ['todo' => [], 'in_progress' => [], 'done' => []];
    $board['board_notes'] = is_array($board['board_notes'] ?? null) ? $board['board_notes'] : [];

    return $board;
}

function normalized_status(string $status): string
{
    $allowed = ['todo', 'in_progress', 'done'];
    return in_array($status, $allowed, true) ? $status : 'todo';
}

function clean_board_order(array $board): array
{
    $ideas = $board['ideas'] ?? [];
    $order = $board['order'] ?? [];
    $statuses = ['todo', 'in_progress', 'done'];

    $seen = [];
    foreach ($statuses as $status) {
        $order[$status] = array_values(array_filter((array)($order[$status] ?? []), static function ($id) use (&$seen, $ideas) {
            if (!isset($ideas[$id]) || isset($seen[$id])) {
                return false;
            }
            $seen[$id] = true;
            return true;
        }));
    }

    foreach ($ideas as $id => $idea) {
        if (!isset($seen[$id])) {
            $status = normalized_status((string)($idea['status'] ?? 'todo'));
            $order[$status][] = $id;
            $board['ideas'][$id]['status'] = $status;
        }
    }

    $board['order'] = $order;

    return $board;
}
