<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>
<?php include "templates/admin/include/message.php" ?>

<table>
    <tr>
        <th>User</th>
        <th>Activity</th>
    </tr>

    <?php foreach ($results['users'] as $user) { ?>
        <tr onclick="location='admin.php?action=editUser&amp;userLogin=<?php echo $user->login ?>'">
            <td>
                <?php echo $user->login ?>
            </td>
            <td>
                <?php echo $user->active ? 'Yes' : 'No'?>
            </td>
        </tr>
    <?php } ?>

</table>

<p><?php echo $results['totalRows']?> user<?php echo ($results['totalRows'] != 1) ? 's' : '' ?> in total.</p>

<p><a href="admin.php?action=newUser">Add a New User</a></p>

<?php include "templates/include/footer.php" ?>
