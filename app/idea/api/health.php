<?php

declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';

json_response(['ok' => true, 'time' => gmdate('c')]);
