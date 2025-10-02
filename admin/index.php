<?php
require_once 'auth.php';
if (!isset($_SESSION['admin'])) die('Not authorized.');

require_once __DIR__ . '/../minerva-config.php';
$contentRoot = $minervaConfig['content_dir'];
$requestedPath = isset($_GET['book']) ? trim($_GET['book'], '/') : '';
$currentPath = realpath($contentRoot . '/' . $requestedPath);

// Security check: Ensure path is inside content root
if (strpos($currentPath, realpath($contentRoot)) !== 0) {
    die('Invalid path');
}

function breadcrumbs($path) {
    $parts = explode('/', trim($path, '/'));
    $crumbs = [];
    $accum = '';

    foreach ($parts as $part) {
        $accum .= ($accum ? '/' : '') . $part;
        $crumbs[] = "<a href='?book=" . urlencode($accum) . "'>" . htmlspecialchars($part) . "</a>";
    }

    return implode(' <span class="text-muted">›</span> ', $crumbs);
}

function listDirectory($dirPath, $baseRel = '') {
    $items = array_diff(scandir($dirPath), ['.', '..']);
    natcasesort($items);
    $html = '';

    foreach ($items as $item) {
        if ($item[0] === '.') continue;

        $fullPath = $dirPath . '/' . $item;
        $relative = ltrim($baseRel . '/' . $item, '/');

        if (is_dir($fullPath)) {
            $encoded = urlencode($relative);
            $html .= "<li class='list-group-item d-flex justify-content-between align-items-center'>";
            $html .= "<div><i class='fa-sharp-duotone fa-regular fa-folder me-2'></i> <a href='?book=$encoded'>" . htmlspecialchars($item) . "</a></div>";
            $html .= "</li>";
        } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'md') {
            $encoded = urlencode($relative);
            $html .= "<li class='list-group-item d-flex justify-content-between align-items-center'>";
            $html .= "<div><i class='fa-sharp-duotone fa-regular fa-file-lines me-2'></i> <a href='edit.php?file=$encoded'>" . htmlspecialchars($item) . "</a></div>";
            $html .= "<div class='btn-group btn-group-sm'>";
            $html .= "<a href='move.php?page=$encoded' class='btn btn-outline-secondary'>Move</a>";
            $html .= "<a href='delete.php?page=$encoded' class='btn btn-outline-danger' onclick='return confirm(\"Are you sure you want to delete $item?\")'>Delete</a>";
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
<?php require "zz-nav.php"; ?>

<div class="container content-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fa-sharp-duotone fa-regular fa-books"></i>
            <?php if ($requestedPath): ?>
                <a href="?book=" class="btn btn-link text-decoration-none">&laquo; Root</a>
                <?= breadcrumbs($requestedPath) ?>
            <?php else: ?>
                Page Manager
            <?php endif; ?>
        </h1>
        <div class="btn-group">
            <a href="new.php" class="btn btn-success"><i class="fa-sharp-duotone fa-regular fa-plus"></i> New Page</a>
            <a href="tools.php" class="btn btn-outline-secondary"><i class="fa fa-wrench"></i> Admin Tools</a>
        </div>
    </div>

    <ul class="list-group">
        <?php
        if (!$requestedPath):
            $items = array_diff(scandir($contentRoot), ['.', '..']);
            natcasesort($items);
            foreach ($items as $item) {
                if ($item[0] === '.') continue;
                $fullPath = $contentRoot . '/' . $item;
                if (!is_dir($fullPath)) continue;

                $encoded = urlencode($item);
                $isPublic = file_exists("$fullPath/.public");

                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                echo "<div><i class='fa-sharp-duotone fa-regular fa-folder me-2'></i> <a href='?book=$encoded'><strong>" . htmlspecialchars($item) . "</strong></a> ";
                echo "<span class='badge bg-" . ($isPublic ? 'success' : 'secondary') . " ms-2'>" . ($isPublic ? 'Public' : 'Private') . "</span></div>";
                echo "<div class='btn-group btn-group-sm'>";
                echo "<a href='toggle_public.php?book=$encoded' class='btn btn-outline-primary'>";
                echo $isPublic ? "Make Private" : "Make Public";
                echo "</a></div></li>";
            }
        else:
            echo listDirectory($currentPath, $requestedPath);
        endif;
        ?>
    </ul>
</div>

</body>
</html>