<?php

declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../lib/storage.php';

require_auth_or_401();

$board = clean_board_order(read_board((string)$config['data_file']));
json_response(['ok' => true, 'board' => $board]);
