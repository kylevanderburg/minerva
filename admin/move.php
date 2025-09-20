<?php
session_start();
if (!isset($_SESSION['admin'])) die('Not authorized.');

$contentRoot = realpath(__DIR__ . '/../content');
$page = $_GET['page'] ?? '';
$oldPath = realpath("$contentRoot/$page");

if (!$oldPath || strpos($oldPath, $contentRoot) !== 0 || !is_file($oldPath)) {
    die("Invalid source file.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newRelPath = trim($_POST['new_path']);
    $newPath = $contentRoot . '/' . $newRelPath;

    if (file_exists($newPath)) {
        $error = "Target file already exists!";
    } elseif (!is_dir(dirname($newPath))) {
        mkdir(dirname($newPath), 0777, true);
    }

    if (!isset($error) && rename($oldPath, $newPath)) {
        header("Location: index.php?msg=moved");
        exit;
    } else {
        $error = $error ?? "Failed to move file.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Move Markdown File</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h1>Move File</h1>
    <p>Moving: <code><?= htmlspecialchars($page) ?></code></p>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="post">
        <div class="mb-3">
            <label for="new_path" class="form-label">New Path (relative to <code>/content/</code>):</label>
            <input type="text" name="new_path" id="new_path" class="form-control" required>
        </div>
        <button class="btn btn-primary">Move</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</body>
</html>
