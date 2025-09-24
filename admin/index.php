<?php
require_once 'auth.php';

if (!isset($_SESSION['admin'])) die('Not authorized.');

require_once __DIR__ . '/../minerva-config.php';
$contentRoot = $minervaConfig['content_dir'];

function listMarkdownFiles($dir, $base = '') {
    $items = array_diff(scandir($dir), ['.', '..']);
    natcasesort($items);

    $html = '';

    foreach ($items as $item) {
        if ($item[0] === '.') continue;
        $fullPath = $dir . '/' . $item;
        $relative = ltrim($base . '/' . $item, '/');

        if (is_dir($fullPath)) {
            $isTopLevel = strpos($relative, '/') === false;
            $publicFile = $fullPath . '/.public';
            $isPublic = file_exists($publicFile);

            $html .= "<li class='list-group-item d-flex justify-content-between align-items-center'>";
            $html .= "<div><strong>$item/</strong>";

            if ($isTopLevel) {
                $statusBadge = $isPublic
                    ? "<span class='badge bg-success ms-2'>Public</span>"
                    : "<span class='badge bg-secondary ms-2'>Private</span>";
                $html .= $statusBadge;
            }

            $html .= "</div>";

            if ($isTopLevel) {
                $html .= "<div class='btn-group btn-group-sm' role='group'>";
                $html .= "<a href='toggle_public.php?book=" . urlencode($item) . "' class='btn btn-outline-primary'>";
                $html .= $isPublic ? "Make Private" : "Make Public";
                $html .= "</a></div>";
            }

            $html .= "</li>";
            $html .= "<ul class='list-group list-group-flush ms-3'>" . listMarkdownFiles($fullPath, $relative) . "</ul>";
        } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'md') {
            $encoded = urlencode($relative);
            $html .= "<li class='list-group-item d-flex justify-content-between align-items-center'>";
            $html .= "<div><a href='edit.php?file=$encoded'>$relative</a></div>";
            $html .= "<div class='btn-group btn-group-sm' role='group'>";
            $html .= "<a href='move.php?page=$encoded' class='btn btn-outline-secondary'>Move</a>";
            $html .= "<a href='delete.php?page=$encoded' class='btn btn-outline-danger' onclick='return confirm(\"Are you sure you want to delete $relative?\")'>Delete</a>";
            $html .= "</div></li>";
        }
    }

    return $html;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $minervaConfig['site_name']; ?> Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://kit.fontawesome.com/dda4946917.js" crossorigin="anonymous"></script>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= $minervaConfig['favicon_url'] ?>">
    <style>
        .content-box {
            max-width: 1000px;
            margin: 2rem auto;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-light bg-danger ">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><img src="<?= $minervaConfig['logo_url']; ?>" alt="Logo" height="24" class="me-2">
    <?= $minervaConfig['site_name']; ?> Admin</a>
        <a class="btn btn-outline-light" href="logout.php">Logout</a>
    </div>
</nav>

<div class="container content-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fa fa-books"></i> Page Manager</h1>
        <a href="new.php" class="btn btn-success"><i class="fa fa-plus"></i> New Page</a>
    </div>

    <ul class="list-group list-group-flush">
        <?= listMarkdownFiles($contentRoot); ?>
    </ul>

</div>

</body>
</html>
