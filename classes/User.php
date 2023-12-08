<?php

/**
 * Класс для обработки пользователей
 */
class User
{
    // Устанавливаем название таблицы БД, с которой работает класс User
    const NAMETABLE = "users";

    /**
     * @var string Логин пользователя
     */
    public $login  = null;

    /**
     * @var string Пароль пользователя
     */
    public $password = null;

    /**
     * @var int Активность пользователя (1 - пользователь активен, может добавлять,
     * редактировать и удалять статьи и категории; 0 - пользователь заблокирован)
     */
    public $active = null;

    /**
     * Устанавливаем свойства объекта с использованием значений в передаваемом массиве
     *
     * @param assoc Значения свойств
     */
    public function __construct($data = array())
    {
        if (isset($data['login'])) {
            $this->login = $data['login'];
        }

        if (isset($data['password'])) {
            $this->password = $data['password'];
        }

        if (isset($data['active'])) {
            $this->active = (int) $data['active'];
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
     * Возвращаем объект User, соответствующий заданному login
     *
     * @param string Логин пользователя
     * @return User|false Объект User object или false, если запись не была найдена или в случае другой ошибки
     */
    public static function getByLogin($login)
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        // $sql = "SELECT login, active FROM " . self::NAMETABLE . " WHERE login = :login";
        $sql = "SELECT * FROM " . self::NAMETABLE . " WHERE login = :login";

        $st = $conn->prepare($sql);
        $st->bindValue(":login", $login, PDO::PARAM_STR);
        $st->execute();
        $row = $st->fetch();
        $conn = null;

        if ($row) {
            return new User($row);
        }
    }

    /**
     * Возвращаем все (или диапазон) объектов User из базы данных
     *
     * @param int Optional Количество возвращаемых строк (по умолчаниюt = all)
     * @param string Optional Столбец, по которому сортируются пользователи(по умолчанию = "name ASC")
     * @return Array|false Двух элементный массив: results => массив с объектами User; totalRows => общее количество пользователей
     */
    public static function getList($numRows = 1000000, $order = "login ASC")
    { 
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $fromPart = "FROM " . self::NAMETABLE;

        // Никому, кроме админа, не будем передавать в представление пароли и активности пользователей
        if ($_SESSION['username'] == ADMIN_USERNAME) {
            $sql = "SELECT * $fromPart ORDER BY $order LIMIT :numRows";
        } else {
            $sql = "SELECT login $fromPart ORDER BY $order LIMIT :numRows";
        }

        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        $st->execute();
        $list = array();

        while ($row = $st->fetch()) {
            $user = new User($row);
            $list[] = $user;
        }

        // Получаем общее количество пользователей
        $sql = "SELECT COUNT(*) AS totalRows $fromPart";
        $totalRows = $conn->query($sql)->fetch();
        $conn = null;

        return array("results" => $list, "totalRows" => $totalRows[0]);
    }

    /**
     * Вставляем текущий объект User в базу данных
     */
    public function insert()
    {
        // У объекта User есть login?
        if (is_null($this->login)) {
            trigger_error("User::insert(): "
                . "Attempt to insert a User object "
                . "that does not have its login property set.", E_USER_ERROR );
        }

        // Вставляем пользователя
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "INSERT INTO " . self::NAMETABLE
            . " (login, password, active) VALUES (:login, :password, :active)";

        $st = $conn->prepare($sql);
        $st->bindValue(":login", $this->login, PDO::PARAM_STR);
        $st->bindValue(":password", $this->password, PDO::PARAM_STR);
        $st->bindValue(":active", $this->active, PDO::PARAM_INT);
        $st->execute();
        $conn = null;
    }

    /**
     * Обновляем текущий объект User в базе данных
     */
    public function update($userLogin)
    {
        // У объекта User есть login?
        if (is_null($this->login)) {
            trigger_error("User::update(): "
                . "Attempt to update a User object "
                . "that does not have its login property set.", E_USER_ERROR);
        }

        // Обновляем пользователя
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "UPDATE " . self::NAMETABLE
            . " SET login=:login, password = :password, active = :active WHERE login = :userLogin";

        $st = $conn->prepare($sql);
        $st->bindValue(":userLogin", $userLogin, PDO::PARAM_STR);
        $st->bindValue(":password", $this->password, PDO::PARAM_STR);
        $st->bindValue(":active", $this->active, PDO::PARAM_INT);
        $st->bindValue(":login", $this->login, PDO::PARAM_STR);
        $st->execute();
        $conn = null;
    }

    /**
     * Удаляем текущий объект User из базы данных
     */
    public function delete()
    {
        // У объекта User есть login?
        if (is_null($this->login)) {
            trigger_error("User::delete(): "
                . "Attempt to delete a User object "
                . "that does not have its login property set.", E_USER_ERROR);
        }

        // Удаляем пользователя
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "DELETE FROM " . self::NAMETABLE . " WHERE login = :login LIMIT 1";
        
        $st = $conn->prepare($sql);
        $st->bindValue(":login", $this->login, PDO::PARAM_STR);
        $st->execute();
        $conn = null;
    }
}
