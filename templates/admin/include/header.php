
            <div id="adminHeader">
                <h2>Widget News Admin</h2>
                <p>You are logged in as <b><?php echo htmlspecialchars($_SESSION['username']) ?></b>.
                    <a href="admin.php?action=listArticles">Show Articles</a>
                    <a href="admin.php?action=listCategories">Show Categories</a>
<?php if ($_SESSION['username'] == ADMIN_USERNAME) { ?>
                    <a href="admin.php?action=listUsers">Show Users</a>
<?php } ?>
                    <a href="admin.php?action=logout"?>Log Out</a>
                </p>
            </div>
