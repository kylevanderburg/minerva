<?php
require_once 'auth.php';
if (!isset($_SESSION['admin'])) die('Not authorized.');

require_once __DIR__ . '/../minerva-config.php';
$contentRoot = $minervaConfig['content_dir'];
$message = '';

function deleteEmptyDirs($path) {
    $count = 0;
    foreach (array_diff(scandir($path), ['.', '..']) as $item) {
        $fullPath = $path . DIRECTORY_SEPARATOR . $item;
        if (is_dir($fullPath)) {
            $count += deleteEmptyDirs($fullPath); // recurse
            if (count(array_diff(scandir($fullPath), ['.', '..'])) === 0) {
                rmdir($fullPath);
                $count++;
            }
        }
    }
    return $count;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_empty_dirs'])) {
        $deleted = deleteEmptyDirs($contentRoot);
        $message = "$deleted empty director" . ($deleted === 1 ? "y" : "ies") . " deleted.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $minervaConfig['site_name']; ?> Admin Tools</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://kit.fontawesome.com/dda4946917.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= $minervaConfig['favicon_url'] ?>">
    <style>
        .content-box {
            max-width: 800px;
            margin: 2rem auto;
        }
    </style>
</head>
<body>
<?php require "zz-nav.php"; ?>

<div class="container content-box">
    <h1 class="h3 mb-4"><i class="fa fa-tools"></i> Admin Tools</h1>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" class="mb-4">
        <button type="submit" name="delete_empty_dirs" class="btn btn-danger">
            <i class="fa fa-folder-minus"></i> Delete Empty Directories
        </button>
        <p class="form-text text-muted mt-1">Searches the <code>/content/</code> directory and deletes any empty folders.</p>
    </form>

    <!-- Future tools can go here -->
    <p class="text-muted">More tools coming soon.</p>
</div>
</body>
</html>
