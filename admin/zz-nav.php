<nav class="navbar navbar-light bg-danger">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin/">
            <img src="<?= $minervaConfig['logo_url']; ?>" alt="Logo" height="24" class="me-2">
            <?= $minervaConfig['site_name']; ?> Admin
        </a>
        <div class="btn-group" role="group">
            <a class="btn btn-outline-light" href="new.php">
                <i class="fa fa-plus"></i> New Page
            </a>
            <a class="btn btn-outline-light" href="tools.php">
                <i class="fa fa-tools"></i> Tools
            </a>
            <a class="btn btn-outline-light" href="logout.php">
                <i class="fa fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</nav>
