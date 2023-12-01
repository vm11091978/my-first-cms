<?php

require("config.php");
session_start();
$action = isset($_GET['action']) ? $_GET['action'] : "";
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";

if ($action != "login" && $action != "logout" && !$username) {
    login();
    exit;
}

switch ($action) {
    case 'login':
        login();
        break;
    case 'logout':
        logout();
        break;
    case 'newArticle':
        newArticle();
        break;
    case 'editArticle':
        editArticle();
        break;
    case 'deleteArticle':
        deleteArticle();
        break;
    case 'listCategories':
        listCategories();
        break;
    case 'newCategory':
        newCategory();
        break;
    case 'editCategory':
        editCategory();
        break;
    case 'deleteCategory':
        deleteCategory();
        break;
    case 'listSubcategories':
        listSubcategories();
        break;
    case 'newSubcategory':
        newSubcategory();
        break;
    case 'editSubcategory':
        editSubcategory();
        break;
    case 'deleteSubcategory':
        deleteSubcategory();
        break;
    // Далее идёт функционал, доступный только админу
    case 'listUsers':
        if ($_SESSION['username'] == ADMIN_USERNAME) {
            listUsers();
            break;
        }
    case 'newUser':
        if ($_SESSION['username'] == ADMIN_USERNAME) {
            newUser();
            break;
        }
    case 'editUser':
        if ($_SESSION['username'] == ADMIN_USERNAME) {
            editUser();
            break;
        }
    case 'deleteUser':
        if ($_SESSION['username'] == ADMIN_USERNAME) {
            deleteUser();
            break;
        }
    default:
        listArticles();
}

/**
 * Авторизация пользователя или админа -- установка значения в сессию
 */
function login()
{
    $results = array();
    $results['pageTitle'] = "Admin Login | Widget News";

    if (isset($_POST['login'])) {

        // Пользователь получает форму входа: попытка авторизировать пользователя

        if ($_POST['username'] == ADMIN_USERNAME 
            && $_POST['password'] == ADMIN_PASSWORD) {

            // Вход прошел успешно: создаем сессию и перенаправляем на страницу администратора
            $_SESSION['username'] = ADMIN_USERNAME;
            header("Location: admin.php");

        // Может быть авторизироваться пытается не админ, а зарегестрированный пользователь
        } else {
            $user = User::getByLogin($_POST['username']);

            // Если такой пользователь существует и пароль для него совпал
            if ($user && $_POST['password'] == $user->password) {
                // Проверим пользователя на активность
                if ($user->active == 1) {

                    // Вход прошел успешно: создаём сессию и перенаправляем на страницу администратора
                    $_SESSION['username'] = $_POST['username'];
                    header("Location: admin.php");

                } else {

                    // Вход запрещён админом: выводим сообщение об ошибке для пользователя
                    $results['errorMessage'] = "Ваш аккаунт забокирован, обратитесь к администратору.";
                    require(TEMPLATE_PATH . "/admin/loginForm.php");
                }
            } else {

                // Ошибка входа: выводим сообщение об ошибке для пользователя
                $results['errorMessage'] = "Неправильный логин или пароль, попробуйте ещё раз.";
                require(TEMPLATE_PATH . "/admin/loginForm.php");
            }
        }
    } else {

      // Пользователь еще не получил форму: выводим форму
      require(TEMPLATE_PATH . "/admin/loginForm.php");
    }
}

function logout() {
    unset($_SESSION['username']);
    header("Location: admin.php");
}

/**
 * Проверяет, действительно ли существует в БД текущий пользователь и активен ли его аккаунт
 * Если да, то функция возвращает true и код метода, в котором данная функция была вызвана, продолжает выполняться дальше
 * Если нет, то пользователя перекидывает на страницу входа в админку
 *
 * @return true | login()
 */
function isAllow()
{
    if ($_SESSION['username'] == ADMIN_USERNAME) {
        return true;
    }
    $currentUser = User::getByLogin($_SESSION['username']);
    if (isset($currentUser) && $currentUser->active == 1) {
        return true;
    }

    login();
}


/*
 * Методы для работы со статьями
 */

function listArticles()
{
    if (empty(isAllow())) {
        return;
    }

    $results = array();

    $data = Article::getList();
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];

    $data = Category::getList();
    $results['categories'] = array();

    foreach ($data['results'] as $category) {
        $results['categories'][$category->id] = $category;
    }

    $data = Subcategory::getList();
    $results['subcategories'] = array();

    foreach ($data['results'] as $subcategory) {
        $results['subcategories'][$subcategory->id] = $subcategory;
    }

    $results['pageTitle'] = "All articles";

    // вывод сообщения об ошибке (если есть)
    if (isset($_GET['error'])) {
        if ($_GET['error'] == "articleNotFound") 
            $results['errorMessage'] = "Error: Article not found.";
    }

    // вывод сообщения (если есть)
    if (isset($_GET['status'])) {
        if ($_GET['status'] == "changesSaved") {
            $results['statusMessage'] = "Your changes have been saved.";
        }
        if ($_GET['status'] == "articleDeleted")  {
            $results['statusMessage'] = "Article deleted.";
        }
    }

    require(TEMPLATE_PATH . "/admin/listArticles.php");
}

function newArticle()
{
    if (empty(isAllow())) {
        return;
    }

    $results = array();
    $results['pageTitle'] = "New Article";
    $results['formAction'] = "newArticle";

    if (isset($_POST['saveChanges'])) {
//            echo "<pre>";
//            print_r($results);
//            print_r($_POST);
//            echo "<pre>";
//            В $_POST данные о статье сохраняются корректно
        // Пользователь получает форму редактирования статьи: сохраняем новую статью
        $article = new Article();
        $article->storeFormValues( $_POST );
//            echo "<pre>";
//            print_r($article);
//            echo "<pre>";
//            А здесь данные массива $article уже неполные(есть только Число от даты, категория и полный текст статьи)          
        $article->insert();
        header("Location: admin.php?status=changesSaved");

    } elseif (isset($_POST['cancel'])) {

        // Пользователь сбросил результаты редактирования: возвращаемся к списку статей
        header("Location: admin.php");
    } else {

        // Пользователь еще не получил форму редактирования: выводим форму
        $results['article'] = new Article;
        $data = Category::getList();
        $results['categories'] = $data['results'];
        $data = Category::getList();
        $data = Subcategory::getList();
        $results['subcategories'] = $data['results'];

        require(TEMPLATE_PATH . "/admin/editArticle.php");
    }
}

/**
 * Редактирование статьи
 * 
 * @return null
 */
function editArticle()
{
    if (empty(isAllow())) {
        return;
    }

    // Если URL-ссылка ведёт на страницу с несуществующим Id статьи
    if (isset($_GET['articleId']) && ! Article::getById((int)$_GET['articleId'])) {
        header("Location: admin.php?error=articleNotFound");
        return;
    }

    $results = array();
    $results['pageTitle'] = "Edit Article";
    $results['formAction'] = "editArticle";

    if (isset($_POST['saveChanges'])) {

        // Пользователь получил форму редактирования статьи: сохраняем изменения
        if (! $article = Article::getById((int)$_POST['articleId'])) {
            header("Location: admin.php?error=articleNotFound");
            return;
        }

        $article->storeFormValues($_POST);
        $article->update();
        header("Location: admin.php?status=changesSaved");

    } elseif (isset($_POST['cancel'])) {

        // Пользователь отказался от результатов редактирования: возвращаемся к списку статей
        header("Location: admin.php");
    } else {

        // Пользвоатель еще не получил форму редактирования: выводим форму
        $results['article'] = Article::getById((int)$_GET['articleId']);
        $data = Category::getList();
        $results['categories'] = $data['results'];
        $data = Subcategory::getList();
        $results['subcategories'] = $data['results'];

        require(TEMPLATE_PATH . "/admin/editArticle.php");
    }
}

function deleteArticle()
{
    if (empty(isAllow())) {
        return;
    }

    if (! $article = Article::getById((int)$_GET['articleId'])) {
        header("Location: admin.php?error=articleNotFound");
        return;
    }

    $article->delete();
    header("Location: admin.php?status=articleDeleted");
}


/*
 * Методы для работы с категориями
 */

function listCategories()
{
    if (empty(isAllow())) {
        return;
    }

    $results = array();
    $data = Category::getList();
    $results['categories'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Article Categories";

    if (isset($_GET['error'])) {
        if ($_GET['error'] == "categoryNotFound") {
            $results['errorMessage'] = "Error: Category not found.";
        }
        if ($_GET['error'] == "categoryContainsArticles") {
            $results['errorMessage'] = "Error: Category contains articles. Delete the articles, or assign them to another category, before deleting this category.";
        }
    }

    if (isset($_GET['status'])) {
        if ($_GET['status'] == "changesSaved") {
            $results['statusMessage'] = "Your changes have been saved.";
        }
        if ($_GET['status'] == "categoryDeleted") {
            $results['statusMessage'] = "Category deleted.";
        }
    }

    require(TEMPLATE_PATH . "/admin/listCategories.php");
}

function newCategory()
{
    if (empty(isAllow())) {
        return;
    }

    $results = array();
    $results['pageTitle'] = "New Article Category";
    $results['formAction'] = "newCategory";

    if (isset($_POST['saveChanges'])) {

        // User has posted the category edit form: save the new category
        $category = new Category;
        $category->storeFormValues($_POST);
        $category->insert();
        header("Location: admin.php?action=listCategories&status=changesSaved");

    } elseif (isset($_POST['cancel'])) {

        // User has cancelled their edits: return to the category list
        header("Location: admin.php?action=listCategories");
    } else {

        // User has not posted the category edit form yet: display the form
        $results['category'] = new Category;
        require(TEMPLATE_PATH . "/admin/editCategory.php");
    }
}

function editCategory()
{
    if (empty(isAllow())) {
        return;
    }

    // Если URL-ссылка ведёт на страницу с несуществующим Id категории
    if (isset($_GET['categoryId']) && ! Category::getById((int)$_GET['categoryId'])) {
        header("Location: admin.php?action=listCategories&error=categoryNotFound");
        return;
    }

    $results = array();
    $results['pageTitle'] = "Edit Article Category";
    $results['formAction'] = "editCategory";

    if (isset($_POST['saveChanges'])) {

        // User has posted the category edit form: save the category changes

        if (! $category = Category::getById((int)$_POST['categoryId'])) {
            header("Location: admin.php?action=listCategories&error=categoryNotFound");
            return;
        }

        $category->storeFormValues($_POST);
        $category->update();
        header("Location: admin.php?action=listCategories&status=changesSaved");

    } elseif (isset($_POST['cancel'])) {

        // User has cancelled their edits: return to the category list
        header("Location: admin.php?action=listCategories");
    } else {

        // User has not posted the category edit form yet: display the form
        $results['category'] = Category::getById((int)$_GET['categoryId']);
        require(TEMPLATE_PATH . "/admin/editCategory.php");
    }
}

function deleteCategory()
{
    if (empty(isAllow())) {
        return;
    }

    if (! $category = Category::getById((int)$_GET['categoryId'])) {
        header("Location: admin.php?action=listCategories&error=categoryNotFound");
        return;
    }

    $articles = Article::getList(1000000, $category->id);

    if ($articles['totalRows'] > 0) {
        header("Location: admin.php?action=listCategories&error=categoryContainsArticles");
        return;
    }

    $category->delete();
    header("Location: admin.php?action=listCategories&status=categoryDeleted");
}


/*
 * Методы для работы с подкатегориями
 */

function listSubcategories()
{
    if (empty(isAllow())) {
        return;
    }

    $results = array();
    $data = Subcategory::getList();
    $results['subcategories'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Article Subcategories";

    if (isset($_GET['error'])) {
        if ($_GET['error'] == "subcategoryNotFound") {
            $results['errorMessage'] = "Error: Subcategory not found.";
        }
        if ($_GET['error'] == "subcategoryContainsArticles") {
            $results['errorMessage'] = "Error: Subcategory contains articles. Delete the articles, or assign them to another subcategory, before deleting this subcategory.";
        }
    }

    if (isset($_GET['status'])) {
        if ($_GET['status'] == "changesSaved") {
            $results['statusMessage'] = "Your changes have been saved.";
        }
        if ($_GET['status'] == "subcategoryDeleted") {
            $results['statusMessage'] = "Subcategory deleted.";
        }
    }

    require(TEMPLATE_PATH . "/admin/listSubcategories.php");
}

function newSubcategory()
{
    if (empty(isAllow())) {
        return;
    }

    $results = array();
    $results['pageTitle'] = "New Article Subcategory";
    $results['formAction'] = "newSubcategory";

    if (isset($_POST['saveChanges'])) {

        // User has posted the subcategory edit form: save the new subcategory
        $subcategory = new Subcategory;
        $subcategory->storeFormValues($_POST);
        $subcategory->insert();
        header("Location: admin.php?action=listSubcategories&status=changesSaved");

    } elseif (isset($_POST['cancel'])) {

        // User has cancelled their edits: return to the subcategory list
        header("Location: admin.php?action=listSubcategories");
    } else {

        // User has not posted the subcategory edit form yet: display the form
        $results['subcategory'] = new Subcategory;
        $data = Category::getList();
        $results['categories'] = $data['results'];

        require(TEMPLATE_PATH . "/admin/editSubcategory.php");
    }
}

function editSubcategory()
{
    if (empty(isAllow())) {
        return;
    }

    // Если URL-ссылка ведёт на страницу с несуществующим Id подкатегории
    if (isset($_GET['subcategoryId']) && ! Subcategory::getById((int)$_GET['subcategoryId'])) {
        header("Location: admin.php?action=listSubcategories&error=subcategoryNotFound");
        return;
    }

    $results = array();
    $results['pageTitle'] = "Edit Article Subcategory";
    $results['formAction'] = "editSubcategory";

    if (isset($_POST['saveChanges'])) {

        // User has posted the subcategory edit form: save the subcategory changes

        if (! $subcategory = Subcategory::getById((int)$_POST['subcategoryId'])) {
            header("Location: admin.php?action=listSubcategories&error=subcategoryNotFound");
            return;
        }

        $subcategory->storeFormValues($_POST);
        $subcategory->update();
        header("Location: admin.php?action=listSubcategories&status=changesSaved");

    } elseif (isset($_POST['cancel'])) {

        // User has cancelled their edits: return to the subcategory list
        header("Location: admin.php?action=listSubcategories");
    } else {

        // User has not posted the subcategory edit form yet: display the form
        $results['subcategory'] = Subcategory::getById((int)$_GET['subcategoryId']);
        $data = Category::getList();
        $results['categories'] = $data['results'];
        
        require(TEMPLATE_PATH . "/admin/editSubcategory.php");
    }
}

function deleteSubcategory()
{
    if (empty(isAllow())) {
        return;
    }

    if (! $subcategory = Subcategory::getById((int)$_GET['subcategoryId'])) {
        header("Location: admin.php?action=listSubcategories&error=subcategoryNotFound");
        return;
    }

    $articles = Article::getList(1000000, null, $subcategory->id);

    if ($articles['totalRows'] > 0) {
        header("Location: admin.php?action=listSubcategories&error=subcategoryContainsArticles");
        return;
    }

    $subcategory->delete();
    header("Location: admin.php?action=listSubcategories&status=subcategoryDeleted");
}


/*
 * Методы для работы с пользователями
 */

function listUsers()
{
    $results = array();
    $data = User::getList();
    $results['users'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "All users";
    
    if (isset($_GET['error'])) {
        if ($_GET['error'] == "userNotFound") {
            $results['errorMessage'] = "Error: User not found.";
        }
        if ($_GET['error'] == "userExists") {
            $results['errorMessage'] = "Error: User with this login already exists.";
        }
    }

    if (isset($_GET['status'])) {
        if ($_GET['status'] == "changesSaved") {
            $results['statusMessage'] = "Your changes have been saved.";
        }
        if ($_GET['status'] == "userDeleted") {
            $results['statusMessage'] = "User deleted.";
        }
    }

    require(TEMPLATE_PATH . "/admin/listUsers.php");
}

function newUser()
{
    $results = array();
    $results['pageTitle'] = "New User";
    $results['formAction'] = "newUser";

    if (isset($_POST['saveChanges'])) {

        // User has posted the user edit form: save the new user

        $user = new User;
        $user->storeFormValues($_POST);

        // Проверим введённый в форму админом логин на уникальность
        $isUser = User::getByLogin($user->login);
        if ($isUser || $user->login == ADMIN_USERNAME) {
            header("Location: admin.php?action=listUsers&error=userExists");
        } else {
            $user->insert();
            header("Location: admin.php?action=listUsers&status=changesSaved");
        }
    } elseif (isset($_POST['cancel'])) {

        // User has cancelled their edits: return to the user list
        header("Location: admin.php?action=listUsers");
    } else {

        // User has not posted the user edit form yet: display the form
        $results['user'] = new User;
        require(TEMPLATE_PATH . "/admin/editUser.php");
    }
}

function editUser()
{
    // Если URL-ссылка ведёт на страницу с несуществующим логином пользователя
    if (isset($_GET['userLogin']) && ! User::getByLogin($_GET['userLogin'])) {
        header("Location: admin.php?action=listUsers&error=userNotFound");
        return;
    }

    $results = array();
    $results['pageTitle'] = "Edit User";
    $results['formAction'] = "editUser";

    if (isset($_POST['saveChanges'])) {

        // User has posted the user edit form: save the user changes

        // Если админ, отправляя форму, пытается обновить данные несуществующего пользователя
        if (! $user = User::getByLogin($_POST['userLogin'])) {
            header("Location: admin.php?action=listUsers&error=userNotFound");
            return;
        }

        // Если админ изменил логин на новый, но новое значение уже занято
        $isUser = User::getByLogin($_POST['login']);
        if (($_POST['userLogin'] != $_POST['login'] && $isUser) || $_POST['login'] == ADMIN_USERNAME) {
            header("Location: admin.php?action=listUsers&error=userExists");
        } else {

            $user->storeFormValues($_POST);
            $user->update($_POST['userLogin']);
            header("Location: admin.php?action=listUsers&status=changesSaved");
        }
    } elseif(isset($_POST['cancel'])) {

        // User has cancelled their edits: return to the user list
        header("Location: admin.php?action=listUsers");
    } else {

        // User has not posted the user edit form yet: display the form
        $results['user'] = User::getByLogin($_GET['userLogin']);
        require(TEMPLATE_PATH . "/admin/editUser.php");
    }
}

function deleteUser()
{
    // Если админ пытается удалить пользователя с несуществующим логином 
    if (! $user = User::getByLogin($_GET['userLogin'])) {
        header("Location: admin.php?action=listUsers&error=userNotFound");
        return;
    }

    $user->delete();
    header("Location: admin.php?action=listUsers&status=userDeleted");
}
