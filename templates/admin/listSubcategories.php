<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>
<?php include "templates/admin/include/message.php" ?>

<table>
    <tr>
        <th>Subcategory</th>
    </tr>

<?php foreach ($results['subcategories'] as $subcategory) { ?>
    <tr onclick="location='admin.php?action=editSubcategory&amp;subcategoryId=<?php echo $subcategory->id ?>'">
        <td>
            <?php echo $subcategory->subname ?>
        </td>
    </tr>
<?php } ?>

</table>

<p><?php echo $results['totalRows']?> subcategor<?php echo ($results['totalRows'] != 1) ? 'ies' : 'y' ?> in total.</p>

<p><a href="admin.php?action=newSubcategory">Add a New Subcategory</a></p>

<?php include "templates/include/footer.php" ?>
