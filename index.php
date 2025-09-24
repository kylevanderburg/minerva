<?php
require_once __DIR__ . '/minerva-config.php';
$contentRoot = $minervaConfig['content_dir'];
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/lib/Parsedown.php';
$parser = new Parsedown();

// --- Path handling ---
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$relativePath = trim($url, '/');
$topLevelBook = $relativePath === '' ? null : explode('/', $relativePath)[0];

$urlparts = explode("/",$relativePath);

$candidates = [];
$renderDynamicHome = false;

if ($relativePath === '') {
    $renderDynamicHome = true;
} else {
    $fullDirPath = $contentRoot . '/' . $relativePath;

    if (is_dir($fullDirPath)) {
        // ✅ Get first .md file alphabetically in this directory
        $files = glob($fullDirPath . '/*.md');
        natsort($files); // natural sort: chapter-2.md comes after chapter-10.md

        if ($files) {
            $firstFile = basename(reset($files));
            $candidates[] = $relativePath . '/' . $firstFile;
        }
    }

    // Still try direct file match
    $candidates[] = $relativePath . '.md';
    $candidates[] = $relativePath;
}


// --- Resolve file ---
$requestedFile = null;
foreach ($candidates as $candidate) {
    $fullPath = $contentRoot . '/' . $candidate;
    $realPath = realpath($fullPath) ?: $fullPath;
    if (is_file($realPath) && strpos($realPath, realpath($contentRoot)) === 0) {
        $requestedFile = $candidate;
        $absolutePath = $realPath;
        break;
    }
}

if (!$requestedFile && $renderDynamicHome) {
    $renderedContent = renderHomePage();
} elseif (!$requestedFile) {
    http_response_code(404);
    $renderedContent = "<h1>404 Not Found</h1><p>The page you’re looking for does not exist.</p>";
} else {
    $currentResolvedFile = $requestedFile;
    $renderedContent = renderMarkdown($absolutePath);
}

// --- Markdown renderer ---
function renderMarkdown($filePath) {
    global $parser;
    $markdown = file_get_contents($filePath);
    return $parser->text($markdown);
}

// --- Dynamic homepage fallback ---
function renderHomePage() {
    $contentRoot = $GLOBALS['minervaConfig']['content_dir'];
    $books = array_filter(scandir($contentRoot), function ($item) use ($contentRoot) {
        return is_dir($contentRoot . '/' . $item)
            && $item !== '.' && $item !== '..'
            && file_exists($contentRoot . '/' . $item . '/' . $GLOBALS['minervaConfig']['public_indicator']);
    });

    natcasesort($books);

    $html = "<h1>Welcome to {$GLOBALS['minervaConfig']['site_name']}</h1>";
    $html .= "<div class='list-group'>";

    foreach ($books as $book) {
        $encoded = rawurlencode($book);
        $html .= "<a class='list-group-item list-group-item-action' href='/$encoded/'><i class='fa-light fa-sharp-duotone fa-book'></i> $book</a>";
    }

    $html .= "</div>";

    return $html;
}


// --- Navigation menu ---
function listFiles($dir, $base = '') {
    $items = array_diff(scandir($dir), ['.', '..']);
    natcasesort($items);

    echo "<ul class='file-tree'>";

    foreach ($items as $item) {
        $fullPath = $dir . '/' . $item;
        $relativePath = ltrim($base . '/' . $item, '/');
        $segments = explode('/', $relativePath);
        $encodedSegments = array_map('rawurlencode', $segments);
        $cleanUrl = '/' . implode('/', $encodedSegments);

        if (substr($cleanUrl, -3) === '.md') {
            $cleanUrl = substr($cleanUrl, 0, -3);
        }

        if (is_dir($fullPath)) {
            $label = basename($item);
            echo "<li><span class='folder'><i class=\"fa-sharp-duotone fa-light fa-folder\"></i> <a href='$cleanUrl'>$label/</a></span>";
            listFiles($fullPath, $relativePath);
            echo "</li>";
        } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'md') {
            if ($item === 'index.md') continue;
            $label = pathinfo($item, PATHINFO_FILENAME);
            echo "<li><span class='file'><i class=\"fa-sharp-duotone fa-light fa-file\"></i> <a href='$cleanUrl'>$label</a></span></li>";
        }
    }

    echo "</ul>";
}


function renderBreadcrumb($relativePath) {
    $segments = $relativePath === '' ? [] : explode('/', $relativePath);
    $path = '';
    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb d-print-none">';

    // Always include "Home" link
    $html .= '<li class="breadcrumb-item"><a href="/">Home</a></li>';

    $last = array_pop($segments);

    foreach ($segments as $segment) {
        $path .= '/' . rawurlencode($segment);
        $label = ucfirst(str_replace('-', ' ', $segment));
        $html .= '<li class="breadcrumb-item"><a href="' . $path . '/">' . $label . '</a></li>';
    }

    if ($last !== null) {
        $label = ucfirst(str_replace('-', ' ', $last));
        $html .= '<li class="breadcrumb-item active" aria-current="page">' . $label . '</li>';
    }

    $html .= '</ol></nav>';
    return $html;
}

function renderPrevNextButtons($relativePath, $requestedFile) {
    global $contentRoot;

    $currentDir = dirname($requestedFile);
    $files = glob($contentRoot . $currentDir . '/*.md');
    natsort($files);
    $files = array_values($files);

    $currentFile = realpath($contentRoot . '/' . $requestedFile);

    $prev = $next = null;
    $index = array_search($currentFile, $files);

    if ($index !== false) {
        if ($index > 0) $prev = $files[$index - 1];
        if ($index < count($files) - 1) $next = $files[$index + 1];
    }

    $html = '<div class="d-flex justify-content-between mt-4">';

    if ($prev) {
        $label = pathinfo($prev, PATHINFO_FILENAME);
        $path = str_replace(realpath($contentRoot), '', realpath($prev));
        $path = ltrim($path, '/');
        $path = preg_replace('/\.md$/', '', $path);
        $html .= '<a class="btn btn-outline-secondary" href="/' . $path . '">&laquo; ' . $label . '</a>';
    } else {
        $html .= '<span></span>';
    }

    if ($next) {
        $label = pathinfo($next, PATHINFO_FILENAME);
        $path = str_replace(realpath($contentRoot), '', realpath($next));
        $path = ltrim($path, '/');
        $path = preg_replace('/\.md$/', '', $path);
        $html .= '<a class="btn btn-outline-secondary" href="/' . $path . '">' . $label . ' &raquo;</a>';
    } else {
        $html .= '<span></span>';
    }

    $html .= '</div>';

    return $html;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $minervaConfig['site_name']; ?> - <?php echo implode(" + ",$urlparts);?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/dda4946917.js" crossorigin="anonymous"></script>
    <link href="https://kylevanderburg.com/assets/kyle18.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= $minervaConfig['favicon_url'] ?>">
    <style>
        body { background: #f8f9fa; }
        main { padding: 2rem; background: white; border-radius: 0.5rem; }
        a { text-decoration: none; }
        a:hover { text-decoration: underline; }
        .list-group-item a { color: #0d6efd; }
        .list-group-item a:hover { color: #0a58ca; }
        pre { background: #eee; padding: 1rem; border-radius: 0.25rem; }
        .file-tree, .file-tree ul {list-style: none; padding-left: 1rem; }
        .file-tree li {margin-bottom: 0.25rem;}
        /* .folder::before {content: "📁 ";} */
        /* .file::before {content: "📄 ";} */
    </style>
</head>
<body>
    <nav class="navbar navbar-secondary bg-secondary mb-3 d-print-none">
        <div class="container-fluid">
            <a class="navbar-brand" href="/" style="color:#fff;"><img src="<?= $minervaConfig['logo_url']; ?>" alt="Logo" height="24" class="me-2">
            <?= $minervaConfig['site_name']; ?></a>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <aside class="col-md-3 d-print-none">
                <h5 class="mb-3">Navigation</h5>
                <ul class="list-group list-group-flush">
                    <?php
                    if ($topLevelBook && is_dir("$contentRoot/$topLevelBook")) {
                        listFiles("$contentRoot/$topLevelBook", $topLevelBook);
                    } else {
                        echo "<p class='text-muted'>Select a book to view its contents.</p>";

                    }
                    ?>
                </ul>
            </aside>
            <main class="col-md-9">
                <?= renderBreadcrumb($relativePath) ?>
                <?= $renderedContent ?>
                <?php if (isset($currentResolvedFile)) echo renderPrevNextButtons($relativePath, $currentResolvedFile); ?>
            </main>

        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Select all table elements on the page
            const tables = document.querySelectorAll("table");
            
            // Loop through each table and add the "table" class
            tables.forEach(function(table) {
                table.classList.add("table");
                table.classList.add("table-bordered");
            });
        });
    </script>
</body>
</html>
