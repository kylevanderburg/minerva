<?php
session_start();
if (!isset($_SESSION['admin'])) die('Not authorized.');

$file = $_GET['file'] ?? '';
$fullPath = realpath(__DIR__ . '/../content/' . $file);

if (!$fullPath || strpos($fullPath, realpath(__DIR__ . '/../content')) !== 0 || !file_exists($fullPath)) {
    die("File not found.");
}

$content = file_get_contents($fullPath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit <?= htmlspecialchars($file) ?> - Minerva Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/dda4946917.js" crossorigin="anonymous"></script>

    <!-- EasyMDE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .editor-container {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
        }

        .editor-container h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .editor-container textarea {
            min-height: 500px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin/">Minerva Admin</a>
        <span class="navbar-text text-light">Editing: <?= htmlspecialchars($file) ?></span>
    </div>
</nav>

<div class="editor-container shadow">
    <h1 class="mb-4">Editing: <?= htmlspecialchars($file) ?></h1>

    <form method="post" action="save.php">
        <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
        <textarea id="markdown-editor" name="content"><?= htmlspecialchars($content) ?></textarea>
        <div class="mt-4 d-flex justify-content-between">
            <a class="btn btn-secondary" href="index.php">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk"></i> Save Changes</button>
        </div>
    </form>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
<script>
    const easyMDE = new EasyMDE({
        element: document.getElementById("markdown-editor"),
        spellChecker: false,
        status: false,
        autosave: {
            enabled: true,
            uniqueId: "<?= md5($file) ?>",
            delay: 1000,
        },
    });
</script>

</body>
</html>
