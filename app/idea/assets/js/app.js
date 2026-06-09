(function ($) {
    'use strict';

    const authed = $('body').data('authed') === 1 || $('body').data('authed') === '1';
    const pollMs = Number($('body').data('poll-ms') || 2000);
    const themeKey = 'idea_board_theme';

    let boardVersion = null;
    let pollingTimer = null;

    function systemPrefersDark() {
        return !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
    }

    function resolveTheme() {
        const saved = localStorage.getItem(themeKey);
        if (saved === 'light' || saved === 'dark') {
            return saved;
        }
        return systemPrefersDark() ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        const next = theme === 'dark' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', next);
        const isDark = next === 'dark';
        const button = $('#themeToggle');
        button.attr('aria-pressed', isDark ? 'true' : 'false');
        button.text(isDark ? 'Light mode' : 'Dark mode');
    }

    function initTheme() {
        applyTheme(resolveTheme());

        $('#themeToggle').on('click', function () {
            const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem(themeKey, next);
            applyTheme(next);
        });

        if (window.matchMedia) {
            const media = window.matchMedia('(prefers-color-scheme: dark)');
            const onChange = function () {
                const saved = localStorage.getItem(themeKey);
                if (saved === 'light' || saved === 'dark') {
                    return;
                }
                applyTheme(systemPrefersDark() ? 'dark' : 'light');
            };

            if (typeof media.addEventListener === 'function') {
                media.addEventListener('change', onChange);
            } else if (typeof media.addListener === 'function') {
                media.addListener(onChange);
            }
        }
    }

    function esc(text) {
        return $('<div>').text(text || '').html();
    }

    function statusLabel(status) {
        if (status === 'in_progress') return 'In Progress';
        if (status === 'done') return 'Done';
        return 'To Do';
    }

    function ideaMarkup(idea) {
        const notes = Array.isArray(idea.notes) ? idea.notes : [];

        const notesHtml = notes
            .slice(0, 3)
            .map((n) => `<li>${esc(n.text)}</li>`)
            .join('');

        return `
            <li class="idea-card" data-id="${esc(idea.id)}">
                <h4>${esc(idea.text)}</h4>
                <div class="meta-row">
                    <span class="status-pill ${esc(idea.status)}">${statusLabel(idea.status)}</span>
                    <select class="status-select" data-id="${esc(idea.id)}">
                        <option value="todo" ${idea.status === 'todo' ? 'selected' : ''}>To do</option>
                        <option value="in_progress" ${idea.status === 'in_progress' ? 'selected' : ''}>In progress</option>
                        <option value="done" ${idea.status === 'done' ? 'selected' : ''}>Done</option>
                    </select>
                </div>
                <form class="idea-note-form" data-id="${esc(idea.id)}">
                    <input type="text" name="text" maxlength="220" placeholder="Add note for this idea" required>
                    <button class="btn" type="submit">Add</button>
                </form>
                <ul class="idea-note-list">${notesHtml}</ul>
            </li>
        `;
    }

    function renderBoard(board) {
        if (!board) {
            return;
        }

        boardVersion = board.version;

        ['todo', 'in_progress', 'done'].forEach((status) => {
            const lane = $(`#lane-${status}`);
            lane.empty();

            const ids = (board.order && board.order[status]) || [];
            ids.forEach((id) => {
                const idea = board.ideas[id];
                if (!idea) return;
                lane.append(ideaMarkup(idea));
            });
        });

        const noteList = $('#boardNotesList');
        noteList.empty();
        (board.board_notes || []).slice(0, 15).forEach((n) => {
            noteList.append(`
                <li class="board-note-item" data-note-id="${esc(n.id)}">
                    <span class="note-text">${esc(n.text)}</span>
                    <button type="button" class="note-delete-btn" data-note-id="${esc(n.id)}" aria-label="Delete note">Delete</button>
                </li>
            `);
        });

        bindDynamicForms();
    }

    function collectOrder() {
        return {
            todo: $('#lane-todo .idea-card').map((_, el) => $(el).data('id')).get(),
            in_progress: $('#lane-in_progress .idea-card').map((_, el) => $(el).data('id')).get(),
            done: $('#lane-done .idea-card').map((_, el) => $(el).data('id')).get()
        };
    }

    function postJson(url, data, onSuccess) {
        $.post(url, data)
            .done((res) => {
                if (!res || !res.ok) {
                    return;
                }
                if (res.board) {
                    renderBoard(res.board);
                }
                if (onSuccess) onSuccess(res);
            });
    }

    function bindDynamicForms() {
        $('.idea-note-form').off('submit').on('submit', function (e) {
            e.preventDefault();
            const ideaId = $(this).data('id');
            const input = $(this).find('input[name="text"]');
            const text = (input.val() || '').toString().trim();
            if (!text) return;

            postJson('api/add_idea_note.php', { idea_id: ideaId, text }, () => {
                input.val('');
            });
        });

        $('.status-select').off('change').on('change', function () {
            const ideaId = $(this).data('id');
            const status = $(this).val();
            postJson('api/set_status.php', { idea_id: ideaId, status });
        });

        $('.note-delete-btn').off('click').on('click', function () {
            const noteId = ($(this).data('note-id') || '').toString().trim();
            if (!noteId) {
                return;
            }

            postJson('api/delete_board_note.php', { note_id: noteId });
        });
    }

    function initSortable() {
        $('.idea-list').sortable({
            connectWith: '.idea-list',
            placeholder: 'sortable-placeholder',
            forcePlaceholderSize: true,
            stop: function () {
                const order = collectOrder();
                postJson('api/move_ideas.php', { order: JSON.stringify(order) });
            }
        }).disableSelection();
    }

    function fetchBoard() {
        $.getJSON('api/board.php')
            .done((res) => {
                if (!res || !res.ok || !res.board) return;
                if (boardVersion !== res.board.version) {
                    renderBoard(res.board);
                }
            });
    }

    function startPolling() {
        if (pollingTimer) clearInterval(pollingTimer);
        pollingTimer = setInterval(fetchBoard, Math.max(pollMs, 1000));
    }

    $(function () {
        initTheme();

        if (!authed) {
            $('#loginForm').on('submit', function (e) {
                e.preventDefault();
                $('#authError').text('');

                $.post('api/login.php', { otp: $('#otpInput').val() })
                    .done((res) => {
                        if (res && res.ok) {
                            window.location.reload();
                            return;
                        }
                        $('#authError').text('Invalid code.');
                    })
                    .fail(() => {
                        $('#authError').text('Invalid code.');
                    });
            });
            return;
        }

        $('#logoutBtn').on('click', function () {
            $.post('api/logout.php').always(() => window.location.reload());
        });

        $('#ideaForm').on('submit', function (e) {
            e.preventDefault();
            const text = ($('#ideaInput').val() || '').toString().trim();
            const status = $('#ideaStatus').val();
            if (!text) return;

            postJson('api/add_idea.php', { text, status }, () => {
                $('#ideaInput').val('');
            });
        });

        $('#boardNoteForm').on('submit', function (e) {
            e.preventDefault();
            const text = ($('#boardNoteInput').val() || '').toString().trim();
            if (!text) return;

            postJson('api/add_board_note.php', { text }, () => {
                $('#boardNoteInput').val('');
            });
        });

        initSortable();
        fetchBoard();
        startPolling();
    });
})(jQuery);
