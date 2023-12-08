# MY FIRST CMS

*Учебный проект "Простая CMS на базе PHP и MySQL" Подробные пояснения, пошаговая инструкция по написанию данной CMS, а также рекомендации для дальнейшей работы можно найти на сайте It For Free: http://fkn.ktu10.com/?q=node/9428*

## Как развернуть:

   1) Загрузите исходный код на ваш компьютер способом, указанным [в начале этой заметки (форк и затем клон форка)](http://fkn.ktu10.com/?q=node/9428)

   2) Открываем проект в своей программе для разработки (например, NetBeans)

   3) Разворачиваем дамп базы данных:
        - сначала создайте в mysql новую базу данных с имененем `cms`
        - а потом разверните в ней дамп из файла `db_cms.sql` (лежит в корне данного проекта): http://fkn.ktu10.com/?q=node/8944

   4) Создаёте в корне проекта файл `config-local.php` и добавьте в него как минимум такое содержимое (укажите пароль к бд):
      ```php
        <?php

        // вместо 1234 укажите свой пароль к базе данных
        $CmsConfiguration["DB_PASSWORD"] = "1234"; // переопределяем пароль к базе данных
       ```

   5) Следуем инструкциям http://fkn.ktu10.com/?q=node/9428
    

Удачной разработки!

## История изменений

 [История изменений в репозитории](CHANGELOG.md).


Для второго практического задания добавляем новый столбец в таблицу "articles":
```php
ALTER TABLE `articles` ADD `active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `content`;
```


Для третьего практического задания создаём новую таблицу "users":
```php
CREATE TABLE `users` (
  `login` VARCHAR(32) NOT NULL UNIQUE,
  `password` VARCHAR(32) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`login`))
  ENGINE = INNODB;
```
Здесь столбец "active" - активность пользователя ("1" - разрешен вход по логину и паролю, "0" - пользователь заблокирован). Редактируется админом.


Для четвёртого практического задания создаём новую таблицу "subcategories" с привязкой по внешнему ключу:
```php
CREATE TABLE `subcategories` (
  `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoryId` SMALLINT(5) UNSIGNED NOT NULL, 
  `subname` VARCHAR(255) NOT NULL, 
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `subcategories_ibfk_1` (`categoryId`)
) ENGINE = INNODB;
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1`
  FOREIGN KEY (`categoryId`)
  REFERENCES `categories` (`id`)
  ON DELETE CASCADE;
```

Необязательно, но таблицу "subcategories" можно сразу заполнить некоторыми тестовыми данными:
```php
INSERT INTO `subcategories` (`categoryId`, `subname`) VALUES
(1, 'некоторая подкатегория'),
(1, 'другая подкатегория'),
(3, 'ещё подкатегория');
```

Так как в таблице "articles" уже существуют статьи, которые непосредственно относятся к какой-то категории минуя подкатегорию, не будем удалять этот столбец и вносить правки в существующие данные таблицы. Реализуем такую логику (в представлении): если у статьи существует categoryId (не равен нулю) и не существует subcategoryId, относим её к этой категории и присваиваем подкатегорию с названием "без подкатегории", иначе определяем её подкатегорию и относим её уже к этой подкатегории. Каждая существующая подкатегория принадлежит какой-то только одной определённой категории.
Вставляем столбец с подкатегориями в таблицу со статьями:
```php
ALTER TABLE `articles` ADD `subcategoryId` SMALLINT(5) DEFAULT NULL AFTER `categoryId`;
```


Для пятого практического задания создаём новую таблицу "user_articles":
```php
CREATE TABLE `user_articles` (
  `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userLogin` VARCHAR(32) NOT NULL,
  `articleId` SMALLINT(5) UNSIGNED NOT NULL, 
  PRIMARY KEY (`id`),
    UNIQUE KEY `relation_row_unique` (`userLogin`, `articleId`),
  INDEX `userLogin` (`userLogin`),
  INDEX `articleId` (`articleId`),
  CONSTRAINT `user_articles_ibfk_1` FOREIGN KEY (`userLogin`) 
    REFERENCES `Users` (`login`) ON DELETE CASCADE,
  CONSTRAINT `user_articles_ibfk_2` FOREIGN KEY (`articleId`) 
    REFERENCES `Articles` (`id`) ON DELETE CASCADE
) ENGINE = INNODB;
```
