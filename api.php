<?php
// /web-tools/editor/api.php  (PHP 8.5 compatible)

// ---------- Autosave API ----------
$baseDir = __DIR__;
$autosaveDir = $baseDir . DIRECTORY_SEPARATOR . 'autosave';

if (!is_dir($autosaveDir)) {
  @mkdir($autosaveDir, 0755, true);
}

function json_out($arr, $code = 200) {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function safe_filename($name) {
  return preg_replace('/[^A-Za-z0-9._-]/', '', $name);
}

function cleanup_autosave_dir($dir) {
  $now = time();
  $files = glob($dir . DIRECTORY_SEPARATOR . '*.json') ?: [];
  foreach ($files as $f) {
    if (@is_file($f) && ($now - @filemtime($f) > 2 * 24 * 3600)) {
      @unlink($f);
    }
  }
}

function autosave_total_bytes($dir) {
  $total = 0;
  $files = glob($dir . DIRECTORY_SEPARATOR . '*.json') ?: [];
  foreach ($files as $f) {
    if (@is_file($f)) $total += (int)@filesize($f);
  }
  return $total;
}

$action = $_GET['action'] ?? '';
if ($action !== '') {
  if ($action === 'save') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) json_out(['ok' => false, 'error' => 'Invalid JSON'], 400);

    $code = (string)($data['code'] ?? '');
    if (strlen($code) > 2 * 1024 * 1024) {
      json_out(['ok' => false, 'error' => 'File size is too large'], 400);
    }

    $ts = (string)($data['ts'] ?? '');
    if ($ts === '') $ts = (string)time();

    if (!is_dir($autosaveDir) || !is_writable($autosaveDir)) {
      json_out(['ok' => false, 'error' => 'autosave directory not writable'], 500);
    }

    cleanup_autosave_dir($autosaveDir);

    $total = autosave_total_bytes($autosaveDir);
    if ($total > 2 * 1024 * 1024 * 1024) {
      json_out(['ok' => false, 'error' => 'Storage quota exceeded'], 400);
    }

    $fname = safe_filename($ts) . '.json';
    $path = $autosaveDir . DIRECTORY_SEPARATOR . $fname;

    $payload = [
      'ts' => $ts,
      'saved_at' => date('c'),
      'code' => $code
    ];

    $ok = @file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    if ($ok === false) json_out(['ok' => false, 'error' => 'write failed'], 500);

    $files = glob($autosaveDir . DIRECTORY_SEPARATOR . '*.json') ?: [];
    usort($files, function($a, $b){ return filemtime($b) <=> filemtime($a); });
    if (count($files) > 20) {
      foreach (array_slice($files, 20) as $old) { @unlink($old); }
    }

    $total2 = autosave_total_bytes($autosaveDir);
    if ($total2 > 2 * 1024 * 1024 * 1024) {
      json_out(['ok' => false, 'error' => 'Storage quota exceeded'], 400);
    }

    json_out(['ok' => true, 'file' => $fname]);
  }

  if ($action === 'list') {
    cleanup_autosave_dir($autosaveDir);

    $files = glob($autosaveDir . DIRECTORY_SEPARATOR . '*.json') ?: [];
    usort($files, function($a, $b){ return filemtime($b) <=> filemtime($a); });

    $items = [];
    foreach (array_slice($files, 0, 20) as $f) {
      $bn = basename($f);
      $items[] = [
        'file' => $bn,
        'mtime' => filemtime($f),
        'mtime_iso' => date('c', filemtime($f)),
        'size' => filesize($f)
      ];
    }
    json_out(['ok' => true, 'items' => $items]);
  }

  if ($action === 'load') {
    $file = safe_filename($_GET['file'] ?? '');
    if ($file === '' || !str_ends_with($file, '.json')) json_out(['ok' => false, 'error' => 'bad file'], 400);

    $path = $autosaveDir . DIRECTORY_SEPARATOR . $file;
    if (!is_file($path)) json_out(['ok' => false, 'error' => 'not found'], 404);

    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    if (!is_array($data)) json_out(['ok' => false, 'error' => 'corrupt file'], 500);

    json_out(['ok' => true, 'data' => $data]);
  }

  json_out(['ok' => false, 'error' => 'unknown action'], 400);
}

json_out(['ok' => false, 'error' => 'unknown action'], 400);