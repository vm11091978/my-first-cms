<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>

            <h1><?php echo $results['pageTitle'] ?></h1>

            <form action="admin.php?action=<?php echo $results['formAction']?>" method="post"> 
            <!-- Обработка формы будет направлена файлу admin.php функции newUser либо editUser
                в зависимости от formAction, сохранённого в result-е -->
                <input type="hidden" name="userLogin" value="<?php echo $results['user']->login ?>" />

                <?php if (isset($results['errorMessage'])) { ?>
                    <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
                <?php } ?>

                <ul>

                    <li>
                        <label for="name">User login</label>
                        <input type="text" name="login" id="login" placeholder="Login of the user" required autofocus maxlength="32" value="<?php echo htmlspecialchars($results['user']->login) ?>" />
                    </li>

                    <li>
                        <label for="name">User password</label>
                        <input type="text" name="password" id="password" placeholder="Password of the user" required autofocus maxlength="32" value="<?php echo htmlspecialchars($results['user']->password) ?>" />
                    </li>

                    <li>
                        <label for="checkActivity">Active</label>
                        <input type="hidden" name="active" value="0">
                        <input id="checkActivity" type="checkbox" name="active" value="1"
                        <?php echo !isset($results['user']->active) || $results['user']->active ? "checked" : "" ?> />
                    </li>

                </ul>

                <div class="buttons">
                    <input type="submit" name="saveChanges" value="Save Changes" />
                    <input type="submit" formnovalidate name="cancel" value="Cancel" />
                </div>

            </form>

    <?php if ($results['user']->login) { ?>
        <p><a href="admin.php?action=deleteUser&amp;userLogin=<?php echo $results['user']->login ?>" onclick="return confirm('Delete This User?')">
                Delete This User
            </a></p>
    <?php } ?>

<?php include "templates/include/footer.php" ?>
