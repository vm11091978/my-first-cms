<?php

/**
 * Класс для обработки подкатегорий статей
 */
class Subcategory
{
    // Устанавливаем название таблицы БД, с которой работает класс Subcategory
    const NAMETABLE = "subcategories";

    /**
     * @var int ID подкатегории из базы данных
     */
    public $id = null;

    /**
     * @var int ID категории, к которой относится данная подкатегория
     */
    public $categoryId = null;

    /**
     * @var string Название категории, к которой относится данная подкатегория (будет взято из связанной таблицы)
     */
    public $name = null;

    /**
     * @var string Название подкатегории
     */
    public $subname = null;

    /**
     * Устанавливаем свойства объекта с использованием значений в передаваемом массиве
     *
     * @param assoc Значения свойств
     */
    public function __construct($data = array())
    {
        if (isset($data['id'])) {
            $this->id = (int) $data['id'];
        }

        if (isset($data['categoryId'])) {
            $this->categoryId = (int) $data['categoryId'];
        }

        if (isset($data['name'])) {
            $this->name = $data['name'];
        }

        if (isset($data['subname'])) {
            $this->subname = $data['subname'];
        }
    }

    /**
     * Устанавливаем свойства объекта с использованием значений из формы редактирования
     *
     * @param assoc Значения из формы редактирования
     */
    public function storeFormValues($params)
    {
        // Store all the parameters
        $this->__construct($params);
    }


    /**
     * Возвращаем объект Subcategory, соответствующий заданному ID
     *
     * @param int ID подкатегории
     * @return Subcategory|false Объект Subcategory object или false, если запись не была найдена или в случае другой ошибки
     */
    public static function getById($id) 
    {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT * FROM " . self::NAMETABLE . " categories WHERE id = :id";

        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch();
        $conn = null;
        if ($row) {
            return new Subcategory($row);
        }
    }

    /**
     * Возвращаем все (или диапазон) объектов Subcategory из базы данных
     *
     * @param int Optional Количество возвращаемых строк (по умолчаниюt = all)
     * @param string Optional Столбец, по которому сортируются подкатегории (по умолчанию = "name ASC")
     * @return Array|false Двух элементный массив: results => массив с объектами Subcategory; totalRows => общее количество подкатегорий
     */
    public static function getList($numRows = 1000000, $order = "subname ASC")
    { 
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $tableName = self::NAMETABLE;
        $fromPart = "FROM " . self::NAMETABLE;
        $sql = "SELECT $tableName.*, categories.name $fromPart
            LEFT JOIN categories
            ON $tableName.categoryId = categories.id
            ORDER BY $order LIMIT :numRows";

        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        $st->execute();
        $list = array();

        while ($row = $st->fetch()) {
            $subcategory = new Subcategory($row);
            $list[] = $subcategory;
        }

        // Получаем общее количество подкатегорий
        $sql = "SELECT COUNT(*) AS totalRows $fromPart";
        $totalRows = $conn->query($sql)->fetch();
        $conn = null;

        return array("results" => $list, "totalRows" => $totalRows[0]);
    }
    
    /**
     * Вставляем текущий объект Subcategory в базу данных и устанавливаем его свойство ID
     */
    public function insert() {

        // У объекта Subcategory уже есть ID?
        if (! is_null($this->id)) {
            trigger_error ("Subcategory::insert(): "
                . "Attempt to insert a Subcategory object "
                . "that already has its ID property set (to $this->id).", E_USER_ERROR);
        }

        // Вставляем подкатегорию
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "INSERT INTO " . self::NAMETABLE 
            . " (categoryId, subname) VALUES (:categoryId, :subname)";

        $st = $conn->prepare($sql);
        $st->bindValue(":categoryId", $this->categoryId, PDO::PARAM_INT);
        $st->bindValue(":subname", $this->subname, PDO::PARAM_STR);
        $st->execute();
        $this->id = $conn->lastInsertId();
        $conn = null;
    }

    /**
     * Обновляем текущий объект Subcategory в базе данных
     */
    public function update() {

        // У объекта Subcategory есть ID?
        if (is_null($this->id)) {
            trigger_error ("Subcategory::update(): "
                . "Attempt to update a Subcategory object "
                . "that does not have its ID property set.", E_USER_ERROR);
        }

        // Обновляем подкатегорию
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "UPDATE " . self::NAMETABLE 
            . " SET categoryId = :categoryId, subname = :subname WHERE id = :id";

        $st = $conn->prepare($sql);
        $st->bindValue(":categoryId", $this->categoryId, PDO::PARAM_INT);
        $st->bindValue(":subname", $this->subname, PDO::PARAM_STR);
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
    }

    /**
     * Удаляем текущий объект Subcategory из базы данных
     */
    public function delete() {

        // У объекта Subcategory есть ID?
        if (is_null($this->id)) {
            trigger_error ("Subcategory::delete(): "
                . "Attempt to delete a Subcategory object "
                . "that does not have its ID property set.", E_USER_ERROR);
        }

        // Удаляем подкатегорию
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "DELETE FROM " . self::NAMETABLE . " WHERE id = :id LIMIT 1";
        
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
    }
}
