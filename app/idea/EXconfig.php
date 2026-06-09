<?php

return [
  // Shared one-time password for everyone accessing the board.
  'otp' => 'password',
  'session_name' => 'idea_board_session',
  'data_file' => __DIR__ . '/data/board.json',
  'poll_interval_ms' => 2000,
];
