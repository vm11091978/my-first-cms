<?php include "templates/include/header.php" ?>

    <h1><?php echo htmlspecialchars($results['pageHeading']) ?></h1>

    <?php if ($results['category']) { ?>
    <h3 class="categoryDescription"><?php echo htmlspecialchars($results['category']->description) ?></h3>
    <?php } ?>

    <ul id="headlines" class="archive">

    <?php foreach ($results['articles'] as $article) { ?>

            <li>
                <h2>
                    <span class="pubDate">
                        <?php echo date('j F Y', $article->publicationDate) ?>
                    </span>
                    <a href=".?action=viewArticle&amp;articleId=<?php echo $article->id ?>">
                        <?php echo htmlspecialchars($article->title) ?>
                    </a>

                    <?php if ($article->categoryId && ! $article->subcategoryId) { ?>
                    <span class="category">
                        in category
                        <a href=".?action=archive&amp;categoryId=<?php echo $article->categoryId ?>">
                            <?php echo htmlspecialchars($results['categories'][$article->categoryId]->name) ?>
                        </a>
                    </span>
                    <?php }
                    elseif ($article->subcategoryId) { ?>
                        <span class="category">
                            in category
                            <a href=".?action=archive&amp;categoryId=<?php echo $results['subcategories'][$article->subcategoryId]->categoryId ?>">
                                <?php echo htmlspecialchars($results['subcategories'][$article->subcategoryId]->name) ?>
                            </a>
                        </span>
                        <span class="category">
                            in subcategory
                            <a href=".?action=archive&amp;subcategoryId=<?php echo $article->subcategoryId ?>">
                                <?php echo htmlspecialchars($results['subcategories'][$article->subcategoryId]->subname) ?>
                            </a>
                        </span>
                    <?php } ?>
                </h2>
                <p class="summary"><?php echo htmlspecialchars($article->summary) ?></p>
            </li>

    <?php } ?>

    </ul>

    <p><?php echo $results['totalRows'] ?> article<?php echo ($results['totalRows'] != 1) ? 's' : '' ?> in total.</p>

    <p><a href="./">Return to Homepage</a></p>

<?php include "templates/include/footer.php" ?>
