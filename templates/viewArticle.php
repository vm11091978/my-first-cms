<?php include "templates/include/header.php" ?>

    <h1 style="width: 75%;"><?php echo htmlspecialchars($results['article']->title) ?></h1>
    <div style="width: 75%; font-style: italic;"><?php echo htmlspecialchars($results['article']->summary) ?></div>
    <div style="width: 75%;"><?php echo $results['article']->content ?></div>
    <p class="pubDate">Published on <?php echo date('j F Y', $results['article']->publicationDate) ?>

    <?php if ($results['category']) { ?>
        in category
        <a href="./?action=archive&amp;categoryId=<?php echo $results['category']->id ?>">
            <?php echo htmlspecialchars($results['category']->name) ?>
        </a>
    <?php }
    elseif ($results['subcategory']) { ?>
        in subcategory
        <a href="./?action=archive&amp;subcategoryId=<?php echo $results['subcategory']->id ?>">
            <?php echo htmlspecialchars($results['subcategory']->subname) ?>
        </a>
    <?php } ?>

    <?php if ($results['article']->authors) { ?>
    <span style="font-size: 80%;" class="category">
        author<?php echo isset($results['article']->authors[1]) ? 's' : '' ?>: 
        <?php echo implode(", ", $results['article']->authors); ?>
    </span>
    <?php } ?>

    </p>

    <p><a href="./">Вернуться на главную страницу</a></p>

<?php include "templates/include/footer.php" ?>
