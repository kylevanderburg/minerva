<?php
session_start();
if (!isset($_SESSION['admin'])) die('Not authorized.');

// Validate file input
$file = $_POST['file'] ?? '';
$relativePath = trim($file, '/');

// Prevent directory traversal
$contentRoot = realpath(__DIR__ . '/../content');
$fullPath = realpath($contentRoot . '/' . $relativePath);
if (!$fullPath || strpos($fullPath, $contentRoot) !== 0) {
    die("Invalid file path.");
}

// Get and save content
$content = $_POST['content'] ?? '';
if (!is_writable($fullPath)) {
    die("File is not writable.");
}

file_put_contents($fullPath, $content);

// Redirect back to editor
header("Location: edit.php?file=" . urlencode($relativePath));
exit;
