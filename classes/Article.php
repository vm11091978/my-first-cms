<?php

/**
 * Класс для обработки статей
 */
class Article
{
    /**
     * @var int ID статей из базы данны
     */
    public $id = null;

    /**
     * @var int Дата первой публикации статьи
     */
    public $publicationDate = null;

    /**
     * @var string Полное название статьи
     */
    public $title = null;

    /**
     * @var int|string ID категории статьи
     */
    public $categoryId = null;

    /**
     * @var int ID категории статьи, полученной из связанной таблицы по ID подкатегории
     */
    public $categoryId2 = null;

    /**
     * @var int ID подкатегории статьи
     */
    public $subcategoryId = null;

    /**
     * @var string Краткое описание статьи
     */
    public $summary = null;

    /**
     * @var string HTML содержание статьи
     */
    public $content = null;

    /**
     * @var int активность статьи (1 - статья активна, показывается на главной 
     * странице и странице архива; 0 - статья не активна, видит только админ)
     */
    public $active = null;

    /**
     * Создаст объект статьи
     * 
     * @param array $data массив значений (столбцов) строки таблицы статей
     */
    public function __construct($data = array())
    {
        if (isset($data['id'])) {
            $this->id = (int) $data['id'];
        }

        if (isset($data['publicationDate'])) {
            $this->publicationDate = (string) $data['publicationDate'];
        }
        //die(print_r($this->publicationDate));

        if (isset($data['title'])) {
            $this->title = $data['title'];
        }

        if (isset($data['categoryId'])) {
            $this->categoryId = $data['categoryId'];
        }

        if (isset($data['categoryId2'])) {
            $this->categoryId = $data['categoryId2'];
        }

        if (isset($data['subcategoryId'])) {
            $this->subcategoryId = (int) $data['subcategoryId'];
        }

        if (isset($data['summary'])) {
            $this->summary = $data['summary'];
        }

        if (isset($data['content'])) {
            $this->content = $data['content'];
        }

        if (isset($data['active'])) {
            $this->active = (int) $data['active'];
        }
    }

    /**
     * Устанавливаем свойства с помощью значений формы редактирования записи в заданном массиве
     *
     * @param assoc Значения записи формы
     */
    public function storeFormValues($params)
    {
        // Сохраняем все параметры
        $this->__construct($params);

        // Разбираем и сохраняем дату публикации
        if (isset($params['publicationDate'])) {
            $publicationDate = explode('-', $params['publicationDate']);

            if (count($publicationDate) == 3) {
                list($y, $m, $d) = $publicationDate;
                $this->publicationDate = mktime(0, 0, 0, $m, $d, $y);
            }
        }
    }


    /**
     * Возвращаем объект статьи соответствующий заданному ID статьи
     *
     * @param int ID статьи
     * @return Article|false Объект статьи или false, если запись не найдена или возникли проблемы
     */
    public static function getById($id)
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate)
            AS publicationDate FROM articles WHERE id = :id";

        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch();
        $conn = null;

        if ($row) { 
            return new Article($row);
        }
    }

    /**
     * Возвращает все (или диапазон) объекты Article из базы данных
     *
     * @param int $numRows Количество возвращаемых строк (по умолчанию = 1000000)
     * @param int $categoryId Вернуть статьи только из категории с указанным ID
     * @param string $order Столбец, по которому выполняется сортировка статей (по умолчанию = "publicationDate DESC")
     * @param int $getActive Вернуть ли только активные статьи (по умолчанию null, т.е. вернуть все статьи)
     * @return Array|false Двух элементный массив: results => массив объектов Article; totalRows => общее количество строк
     */
    public static function getList($numRows = 1000000,
            $categoryId = null, $subcategoryId = null, $order = null, $getActive = null)
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $tableName = "articles";
        $fromPart = "FROM " . $tableName;

        if ($categoryId) {
            $categoryClause = "WHERE ($tableName.categoryId = :categoryId OR subcategories.categoryId = :categoryId)";
        } elseif ($subcategoryId) {
            $categoryClause = "WHERE $tableName.subcategoryId = :subcategoryId";
        } else {
            $categoryClause = "";
        }

        $orderClause = $order ? $order : "publicationDate DESC";

        /**
         * если переменная $getActive не существует, то показать все записи
         * если в $getActive передано значение 0, то показать только неактивные записи
         * если в $getActive передано значение 1, то показать только активные записи */
        if (isset($getActive)) {
            $getActive === 0 ? $flag = 0 : $flag = 1;
            $categoryId || $subcategoryId ? $activeClause = "AND active = $flag" : $activeClause = "WHERE active = $flag";
        } else {
            $activeClause = "";
        }

        $sql = "SELECT $tableName.*,
            UNIX_TIMESTAMP(publicationDate) AS publicationDate,
            subcategories.categoryId AS categoryId2
            $fromPart
            LEFT JOIN subcategories
            ON $tableName.subcategoryId = subcategories.id
            $categoryClause $activeClause
            ORDER BY $orderClause LIMIT :numRows";

        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        /**
         * Можно использовать debugDumpParams() для отладки параметров, 
         * привязанных выше с помощью bind()
         * @see https://www.php.net/manual/ru/pdostatement.debugdumpparams.php
         */

        if ($categoryId) {
            $st->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
        }
        if ($subcategoryId) {
            $st->bindValue(":subcategoryId", $subcategoryId, PDO::PARAM_INT);
        }

        // выполняем запрос к базе данных
        $st->execute();
        $list = array();

        $i = 0;
        while ($row = $st->fetch()) {
            $article = new Article($row);
            $list[] = $article;
            $i++;
        }
        
        return (array(
            "results" => $list,
            "totalRows" => $i
            )
        );
    }

    /**
     * Вставляем текущий объект Article в базу данных, устанавливаем его ID
     */
    public function insert()
    {
        // Есть уже у объекта Article ID?
        if (! is_null($this->id)) {
            trigger_error("Article::insert(): "
                . "Attempt to insert an Article object"
                . "that already has its ID property set (to $this->id).", E_USER_ERROR);
        }

        // Вставляем статью
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "INSERT INTO articles (publicationDate, categoryId, subcategoryId, title, summary, content, active)
            VALUES (FROM_UNIXTIME(:publicationDate), :categoryId, :subcategoryId, :title, :summary, :content, :active)";

        $st = $conn->prepare($sql);
        $st->bindValue(":publicationDate", $this->publicationDate, PDO::PARAM_INT);
        /**
         * Если переденное из представления значение categoryId является числом,
         * значит статья принадлежит непосредственно какой-то категории минуя подкатегорию,
         * запишем это значение в БД в столбец "categoryId",
         * а значение subcategoryId для этой статьи будет отутствовать.
         * Иначе эта статья принадлежит какой-то подкатегории, запишем это значение в БД в столбец "subcategoryId",
         * а значение categoryId для этой статьи будет равно нулю.
         */
        if (is_numeric($this->categoryId)) {
            $st->bindValue(":categoryId", $this->categoryId, PDO::PARAM_INT);
            $st->bindValue(":subcategoryId", NULL);
        } else {
            $st->bindValue(":categoryId", 0);
            $st->bindValue(":subcategoryId", str_replace('sub_', '', $this->categoryId), PDO::PARAM_INT);
        }
        $st->bindValue(":title", $this->title, PDO::PARAM_STR);
        $st->bindValue(":summary", $this->summary, PDO::PARAM_STR);
        $st->bindValue(":content", $this->content, PDO::PARAM_STR);
        $st->bindValue(":active", $this->active, PDO::PARAM_INT);
        $st->execute();
        $this->id = $conn->lastInsertId();
        $conn = null;
    }

    /**
     * Обновляем текущий объект статьи в базе данных
     */
    public function update()
    {
        // Есть ли у объекта статьи ID?
        if (is_null($this->id)) {
            trigger_error("Article::update(): "
                . "Attempt to update an Article object "
                . "that does not have its ID property set.", E_USER_ERROR);
        }

        // Обновляем статью
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "UPDATE articles SET publicationDate = FROM_UNIXTIME(:publicationDate),
            categoryId = :categoryId, subcategoryId = :subcategoryId, title = :title,
            summary = :summary, content = :content, active = :active WHERE id = :id";

        $st = $conn->prepare($sql);
        $st->bindValue(":publicationDate", $this->publicationDate, PDO::PARAM_INT);
        /**
         * Если переденное из представления значение categoryId является числом,
         * значит статья принадлежит непосредственно какой-то категории минуя подкатегорию,
         * запишем это значение в БД в столбец "categoryId",
         * а значение subcategoryId для этой статьи будет отутствовать.
         * Иначе эта статья принадлежит какой-то подкатегории, запишем это значение в БД в столбец "subcategoryId",
         * а значение categoryId для этой статьи будет равно нулю.
         */
        if (is_numeric($this->categoryId)) {
            $st->bindValue(":categoryId", $this->categoryId, PDO::PARAM_INT);
            $st->bindValue(":subcategoryId", NULL);
        } else {
            $st->bindValue(":categoryId", 0);
            $st->bindValue(":subcategoryId", str_replace('sub_', '', $this->categoryId), PDO::PARAM_INT);
        }
        $st->bindValue(":title", $this->title, PDO::PARAM_STR);
        $st->bindValue(":summary", $this->summary, PDO::PARAM_STR);
        $st->bindValue(":content", $this->content, PDO::PARAM_STR);
        $st->bindValue(":active", $this->active, PDO::PARAM_INT);
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
    }

    /**
     * Удаляем текущий объект статьи из базы данных
     */
    public function delete()
    {
        // Есть ли у объекта статьи ID?
        if (is_null($this->id)) {
            trigger_error("Article::delete(): "
            . "Attempt to delete an Article object"
            . "that does not have its ID property set.", E_USER_ERROR);
        }

        // Удаляем статью
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $st = $conn->prepare("DELETE FROM articles WHERE id = :id LIMIT 1");
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
    }
}
