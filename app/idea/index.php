<?php

declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/auth.php';

$authed = is_authenticated();
$pollIntervalMs = (int) ($config['poll_interval_ms'] ?? 2000);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Idea Board</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Space+Grotesk:wght@500;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
  <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
  <link rel="stylesheet" href="assets/css/styles.css">
  <script>
    (function () {
      var key = 'idea_board_theme';
      var saved = localStorage.getItem(key);
      var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      var theme = saved === 'light' || saved === 'dark' ? saved : (prefersDark ? 'dark' : 'light');
      document.documentElement.setAttribute('data-theme', theme);
    })();
  </script>
</head>

<body data-authed="<?= $authed ? '1' : '0' ?>"
  data-poll-ms="<?= htmlspecialchars((string) $pollIntervalMs, ENT_QUOTES, 'UTF-8') ?>">
  <div class="bg-shape bg-shape-one"></div>
  <div class="bg-shape bg-shape-two"></div>

  <main class="app-shell">
    <header class="topbar">
      <h1><img src="assets/img/logo.svg" alt="" class="brand-logo" width="36" height="36"> Idea Board</h1>
      <div class="topbar-actions">
        <button id="themeToggle" class="btn subtle" type="button" aria-pressed="false">Dark mode</button>
        <?php if ($authed): ?>
          <button id="logoutBtn" class="btn subtle" type="button">Sign out</button>
        <?php endif; ?>
      </div>
    </header>

    <?php if (!$authed): ?>
      <section class="auth-card">
        <h2>Shared Access</h2>
        <p>Enter the one-time password.</p>
        <form id="loginForm" autocomplete="off">
          <label for="otpInput">One-time password</label>
          <input id="otpInput" name="otp" type="password" required placeholder="Enter code">
          <button type="submit" class="btn">Enter board</button>
          <p id="authError" class="error-msg"></p>
        </form>
      </section>
    <?php else: ?>
      <section class="controls-grid">
        <article class="panel add-panel">
          <h2>Add Idea</h2>
          <form id="ideaForm">
            <input id="ideaInput" name="text" type="text" maxlength="280" placeholder="Drop your idea here" required>
            <select id="ideaStatus" name="status">
              <option value="todo">To do</option>
              <option value="in_progress">In progress</option>
              <option value="done">Done</option>
            </select>
            <button type="submit" class="btn">Add idea</button>
          </form>
        </article>

        <article class="panel board-notes-panel">
          <h2>Board Notes</h2>
          <form id="boardNoteForm">
            <input id="boardNoteInput" name="text" type="text" maxlength="300" placeholder="Independent note for everyone"
              required>
            <button type="submit" class="btn">Add note</button>
          </form>
          <ul id="boardNotesList" class="note-list"></ul>
        </article>
      </section>

      <section class="board-grid">
        <article class="lane" data-status="todo">
          <header>
            <h3>To Do</h3>
          </header>
          <ul class="idea-list" id="lane-todo"></ul>
        </article>
        <article class="lane" data-status="in_progress">
          <header>
            <h3>In Progress</h3>
          </header>
          <ul class="idea-list" id="lane-in_progress"></ul>
        </article>
        <article class="lane" data-status="done">
          <header>
            <h3>Done</h3>
          </header>
          <ul class="idea-list" id="lane-done"></ul>
        </article>
      </section>
    <?php endif; ?>
  </main>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>
  <script src="assets/js/app.js"></script>
</body>

</html>