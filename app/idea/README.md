# Idea Board (PHP + jQuery)

A shared idea board with:
- OTP-based shared auth from `config.php`
- Drag and drop lanes (To do / In progress / Done)
- Per-idea notes
- Independent board notes
- Live polling updates for all connected viewers
- JSON file persistence (`data/board.json`)

## Setup

1. Put this folder under your web root.
2. Edit `config.php` and set a strong `otp` value.
3. Open `/idea/` in your browser.

## Notes

- Data is saved in `data/board.json`.
- The app uses jQuery and jQuery UI from CDN.
- Polling interval can be changed with `poll_interval_ms` in `config.php`.
