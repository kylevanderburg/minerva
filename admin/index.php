<?php
require_once 'auth.php';

if (!isset($_SESSION['admin'])) die('Not authorized.');

$contentRoot = realpath(__DIR__ . '/../content');

function listMarkdownFiles($dir, $base = '') {
    $items = array_diff(scandir($dir), ['.', '..']);
    natcasesort($items);

    $html = '';

    foreach ($items as $item) {
        $fullPath = $dir . '/' . $item;
        $relative = ltrim($base . '/' . $item, '/');

        if (is_dir($fullPath)) {
            $html .= "<li class='list-group-item'><strong>$item/</strong>";
            $html .= "<ul class='list-group list-group-flush ms-3'>" . listMarkdownFiles($fullPath, $relative) . "</ul></li>";
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
    <title>Minerva Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://kit.fontawesome.com/dda4946917.js" crossorigin="anonymous"></script>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .content-box {
            max-width: 1000px;
            margin: 2rem auto;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><img src="/Minerva.svg" alt="" style="width:50px;filter: invert(100%);" /> Admin</a>
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

    <h2>Manage Markdown Files</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Path</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        function listMarkdownFiles2($dir, $base = '') {
            $items = array_diff(scandir($dir), ['.', '..']);
            natsort($items);
            foreach ($items as $item) {
                $full = "$dir/$item";
                $relative = ltrim("$base/$item", '/');
                if (is_dir($full)) {
                    listMarkdownFiles2($full, $relative);
                } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'md') {
                    echo "<tr>";
                    echo "<td><code>$relative</code></td>";
                    echo "<td>
                        <a class='btn btn-sm btn-outline-primary' href='edit.php?file=" . urlencode($relative) . "'>Edit</a>
                        <a class='btn btn-sm btn-outline-warning' href='move.php?page=" . urlencode($relative) . "'>Move</a>
                        <a class='btn btn-sm btn-outline-danger' href='delete.php?page=" . urlencode($relative) . "' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                    </td>";
                    echo "</tr>";
                }
            }
        }

        listMarkdownFiles2(__DIR__ . '/../content');
        ?>
    </tbody>
</table>

</div>

</body>
</html>
