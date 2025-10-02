<?php
session_start();
if (!isset($_SESSION['admin'])) die('Not authorized.');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = trim($_POST['filename'] ?? '');
    $filename = preg_replace('/\s+/', '-', $filename); // convert whitespace to dashes
    $sanitized = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $filename);
    $sanitized = trim($sanitized, '/');

    if (!$sanitized) {
        $error = "Invalid filename.";
    } else {
        $fullPath = realpath(__DIR__ . '/../content') . '/' . $sanitized . '.md';

        if (file_exists($fullPath)) {
            $error = "That page already exists.";
        } else {
            $dir = dirname($fullPath);
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            file_put_contents($fullPath, "# New Page\n\nStart writing your content here.");
            header("Location: edit.php?file=" . urlencode($sanitized . '.md'));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create New Page - Minerva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 2rem; background: #f8f9fa; }
        .form-box { max-width: 600px; margin: auto; background: white; padding: 2rem; border-radius: 0.5rem; }
    </style>
</head>
<body>

<div class="form-box shadow">
    <h1 class="h4 mb-3">Create New Page</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="filename" class="form-label">Page Path (no extension)</label>
            <input type="text" name="filename" id="filename" class="form-control"
                   placeholder="e.g. book-1/chapter-4" required
                   oninput="this.value = this.value.replace(/\s+/g, '-');">
            <div class="form-text">Allowed: letters, numbers, hyphens, slashes (for folders)</div>
        </div>
        <button type="submit" class="btn btn-primary">Create Page</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>
