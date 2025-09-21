<?php
require_once 'auth.php';
if (!isset($_SESSION['admin'])) die('Not authorized.');
require_once __DIR__ . '/../minerva-config.php';

$contentRoot = $minervaConfig['content_dir'];

if (!isset($_GET['book'])) {
    die('No book specified.');
}

$book = basename($_GET['book']); // sanitize
$bookPath = $contentRoot . '/' . $book;
$publicFile = $bookPath . '/.public';

if (!is_dir($bookPath)) {
    die('Invalid book.');
}

if (file_exists($publicFile)) {
    unlink($publicFile); // make private
} else {
    touch($publicFile); // make public
}

header('Location: /admin/index.php');
exit;
