<?php
session_start();
if (!isset($_SESSION['admin'])) die('Not authorized.');

$contentRoot = realpath(__DIR__ . '/../content');
$page = $_GET['page'] ?? '';

$targetPath = realpath("$contentRoot/$page");

if (!$targetPath || strpos($targetPath, $contentRoot) !== 0 || !is_file($targetPath)) {
    die("Invalid file path.");
}

if (unlink($targetPath)) {
    header("Location: index.php?msg=deleted");
    exit;
} else {
    die("Unable to delete file.");
}
