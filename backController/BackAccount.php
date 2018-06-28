<?php
namespace application\backController;
use application\controllers\AccountController;
use application\models\Test;
use application\models\Result;

class BackAccount
{

    public $db;
    public $model;

    public function __construct($db, $model)
    {
        $this->db = $db;
        $this->model = $model;
    }

    public function loginResponse($login, $password)
    {//Логин пользователя
        $deny_words = array("?", "<", ">");
        $selfLogin = str_replace($deny_words, "[ATTEMPT TO INJECT]", $login, $count);
        if ($this->loginProcess($selfLogin, $password)) {
            echo json_encode(array('response' => 'yes'));
        } else {
            echo json_encode(array('response' => 'no'));
        }
    }

    public function loginProcess($login, $password)
    {

        if (strlen($password) > 25) {
            return false;
        }

        if (strlen($login) > 25) {
            return false;
        }

        $userData = $this->model->getUserData($login, $password);
        if (is_array($userData)) {
            return $this->initUserSession($userData);
        }
    }


    private function initUserSession($user_data)
    {     //Инициализация данных авторизированного
        $_SESSION['USER'] = array(
            'UID' => md5(uniqid(rand(), 1)),
            'USER_ID' => $user_data[0]['USER_ID'],
            'LOGIN' => $user_data[0]['LOGIN'],
            'NAME' => $user_data[0]['USER_NAME'],
            'SURNAME' => $user_data[0]['USER_SURNAME'],
            'PATR' => $user_data[0]['USER_PATR'],
            'GROUP' => $user_data[0]['TITLE'],
            'LEVEL' => $user_data[0]['LEVEL']
        );
        if (isset($_SESSION['USER']) && count($_SESSION['USER']) != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function show($dataset)
    {
        debug($dataset);
    }

    public function getUser()
    {
        return $_SESSION['USER'];
    }


    public function getTest()
    {
        return $_SESSION['TEST'];
    }

    public function getTestData()
    {
        $test = new Test();
        $testTitles = $test->getTestsList();
        return $testTitles;
    }

    public function getResults()
    {
        $resultsObj = new Result();
        $results = $resultsObj->getResultsToUser();
        return $results;
    }

    public function loginUser($login, $password)
    {//Логин пользователя
        $this->initUserSession($array);
    }


}//end class

?>
