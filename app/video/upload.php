<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';
require_once 'cleanup.php';
cleanExpiredFiles();

// Auth check - must be logged in via session
if (empty($_SESSION['authenticated'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Unauthorized. Please enter your access key first.']);
  exit;
}

$uploadDir = __DIR__ . '/uploads/';
$metaDir = __DIR__ . '/meta/';

// Ensure directories exist
if (!is_dir($uploadDir))
  mkdir($uploadDir, 0755, true);
if (!is_dir($metaDir))
  mkdir($metaDir, 0755, true);

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'error' => 'Method not allowed']);
  exit;
}

if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
  $errorMessages = [
    UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit.',
    UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit.',
    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
    UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error.',
    UPLOAD_ERR_CANT_WRITE => 'Failed to write file.',
    UPLOAD_ERR_EXTENSION => 'Upload blocked by server extension.',
  ];
  $code = isset($_FILES['video']) ? $_FILES['video']['error'] : UPLOAD_ERR_NO_FILE;
  $msg = $errorMessages[$code] ?? 'Upload failed.';
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => $msg]);
  exit;
}

$file = $_FILES['video'];

if ($file['size'] > MAX_FILE_SIZE) {
  http_response_code(413);
  echo json_encode(['success' => false, 'error' => 'File is too large. Maximum size is 500MB.']);
  exit;
}

// Validate file type
$allowedMimes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska'];
$allowedExts = ['mp4', 'webm', 'mov', 'avi', 'mkv'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!in_array($ext, $allowedExts)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: MP4, WEBM, MOV, AVI, MKV.']);
  exit;
}

// Resolve expiry tier
$tiers = EXPIRY_TIERS;
$tierKey = isset($_POST['expiry']) ? trim($_POST['expiry']) : '24h';
if (!array_key_exists($tierKey, $tiers)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Invalid expiry option.']);
  exit;
}
$tier = $tiers[$tierKey];

// Enforce per-tier video limit
if ($tier['limit'] !== null) {
  $now = time();
  $activeCount = 0;
  $metaFiles = glob($metaDir . '*.json') ?: [];
  foreach ($metaFiles as $mf) {
    $md = json_decode(file_get_contents($mf), true);
    if (!$md) continue;
    $exp = isset($md['expires_at']) ? (int) $md['expires_at'] : 0;
    if ($exp > $now && isset($md['expiry_tier']) && $md['expiry_tier'] === $tierKey) {
      $activeCount++;
    }
  }
  if ($activeCount >= $tier['limit']) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'The "' . $tier['label'] . '" tier is full (' . $tier['limit'] . ' videos max). Choose a shorter expiry.']);
    exit;
  }
}

// Generate unique ID
$id = bin2hex(random_bytes(16));
$safeOrigName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
$storedName = $id . '.' . $ext;
$destPath = $uploadDir . $storedName;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Failed to save the video. Please try again.']);
  exit;
}

// Save metadata
$expiresAtTs = time() + $tier['seconds'];
$meta = [
  'id' => $id,
  'original_name' => $safeOrigName,
  'stored_name' => $storedName,
  'mime_type' => $mimeType,
  'extension' => $ext,
  'size' => $file['size'],
  'uploaded_at' => time(),
  'expires_at' => $expiresAtTs,
  'expiry_tier' => $tierKey,
];

file_put_contents($metaDir . $id . '.json', json_encode($meta, JSON_PRETTY_PRINT));

echo json_encode([
  'success' => true,
  'id' => $id,
  'expires_at' => $expiresAtTs,
  'expiry_tier' => $tierKey,
]);
