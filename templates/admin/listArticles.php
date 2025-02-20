<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>
<?php include "templates/admin/include/message.php" ?>

          <table>
            <tr>
              <th>Publication Date</th>
              <th>Article</th>
              <th>Category</th>
              <th>Subcategory</th>
              <th>Autors</th>
              <th>Activity</th>
            </tr>

<!--<?php echo "<pre>"; print_r ($results['articles'][2]->publicationDate); echo "</pre>"; ?> Обращаемся к дате массива $results. Дата = 0 -->
<!--<?php echo "<pre>"; print_r ($results); echo "</pre>"; ?> Здесь есть доступ к полному объекту $results -->

    <?php foreach ( $results['articles'] as $article ) { ?>

            <tr onclick="location='admin.php?action=editArticle&amp;articleId=<?php echo $article->id?>'">
              <td>
                <?php echo date('j M Y', $article->publicationDate)?>
              </td>
              <td>
                <?php echo $article->title?>
              </td>

              <td>
              <!-- <?php echo $results['categories'][$article->categoryId]->name?> Эта строка была скопирована с сайта -->
              <!-- <?php echo "<pre>"; print_r ($article); echo "</pre>"; ?> Здесь объект $article содержит в себе только ID категории. А надо по ID достать название категории -->

                <?php
                if (isset($results['subcategories'][$article->subcategoryId]->name)) {
                    echo $results['subcategories'][$article->subcategoryId]->name;
                } elseif (isset($results['categories'][$article->categoryId]->name)) {
                    echo $results['categories'][$article->categoryId]->name;
                } else {
                    echo "Без категории";
                } ?>
              </td>

              <td>
                <?php
                if (isset($results['subcategories'][$article->subcategoryId]->subname)) {
                    echo $results['subcategories'][$article->subcategoryId]->subname;
                } else {
                    echo "-";
                } ?>
              </td>

              <td>
                <?php
                if ($article->authors) {
                    echo implode(", ", $article->authors);
                } else {
                    echo "-";
                } ?>
              </td>

              <td>
                <?php echo $article->active ? 'Yes' : 'No'?>
              </td>
            </tr>

    <?php } ?>

          </table>

          <p><?php echo $results['totalRows']?> article<?php echo ( $results['totalRows'] != 1 ) ? 's' : '' ?> in total.</p>

          <p><a href="admin.php?action=newArticle">Add a New Article</a></p>

<?php include "templates/include/footer.php" ?>
