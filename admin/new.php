<?php
session_start();
if (!isset($_SESSION['admin'])) die('Not authorized.');

/**
 * Normalize a requested filename into safe path segments under content/.
 */
function normalize_path(string $input): string
{
    $input = preg_replace('/\s+/', '-', trim($input));
    $segments = explode('/', $input);

    $normalized = [];
    foreach ($segments as $segment) {
        $clean = preg_replace('/[^a-zA-Z0-9_\-]/', '', $segment);
        $clean = trim($clean, '-_');
        if ($clean !== '') {
            $normalized[] = $clean;
        }
    }

    return implode('/', $normalized);
}

$error = '';
$submittedValue = '';

$contentRoot = realpath(__DIR__ . '/../content');
if ($contentRoot === false) {
    $error = 'Content directory is missing.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $submittedValue = trim($_POST['filename'] ?? '');
    $sanitized = normalize_path($submittedValue);

    if ($sanitized === '') {
        $error = 'Invalid filename.';
    } else {
        $fullPath = $contentRoot . '/' . $sanitized . '.md';

        if (file_exists($fullPath)) {
            $error = 'That page already exists.';
        } else {
            $dir = dirname($fullPath);
            if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
                $error = 'Unable to create folders for that path.';
            } elseif (file_put_contents($fullPath, "# New Page\n\nStart writing your content here.") === false) {
                $error = 'Unable to create the new page file.';
            } else {
                header('Location: edit.php?file=' . urlencode($sanitized . '.md'));
                exit;
            }
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

    <?php
        function getAllSubdirs($baseDir, $relativePath = '') {
            $dirs = [];
            $fullPath = rtrim($baseDir . '/' . $relativePath, '/');

            foreach (scandir($fullPath) as $entry) {
                // Skip hidden files/folders
                if ($entry[0] === '.') continue;

                $subPath = $relativePath ? "$relativePath/$entry" : $entry;
                $absPath = "$baseDir/$subPath";

                if (is_dir($absPath)) {
                    $dirs[] = $subPath;
                    $dirs = array_merge($dirs, getAllSubdirs($baseDir, $subPath));
                }
            }

            return $dirs;
        }


        $books = getAllSubdirs($contentRoot);
        ?>

    <form method="post">
        <div class="mb-3">
            <label for="filename" class="form-label">Page Path (no extension)</label>
            <input type="text" name="filename" id="filename" class="form-control"
                list="book-paths" placeholder="e.g. book-1/chapter-4" required
                value="<?= htmlspecialchars($submittedValue) ?>"
                oninput="this.value = this.value.replace(/\s+/g, '-');">
            <datalist id="book-paths">
                <?php foreach ($books as $book): ?>
                    <option value="<?= htmlspecialchars($book) ?>/">
                <?php endforeach; ?>
            </datalist>
            <div class="form-text">Start typing a book name or choose from the list. Use slashes to create subfolders.</div>
        </div>

        <button type="submit" class="btn btn-primary">Create Page</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>
