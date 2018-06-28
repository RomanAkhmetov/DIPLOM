<?php
namespace application\controllers;
use application\core\Controller;
use application\core\View;
use application\classes\DB;



class AccountController extends Controller{



  public function loginAction(){
  if($this->sessionUser()){//base
      $this->view->redirect('info');
    }
      $this->view->render('Логин');
  }





  public function infoAction(){//base

     if($this->sessionUser()){
         $test=$this->backControl->getTestData();
         $results=$this->backControl->getResults();
         $this->view->render('Личный кабинет',$test,$results);

    //  if(!isset($_SESSION['TEST']['QUESTIONS'])){unset($_SESSION['TEST']);}
  }else{
    $this->view->redirect('/account/login');
   }
 }


//===============================================================



public function logoutAction(){
  session_destroy();
  $this->view->redirect('/account/login');
}


public function checkAction(){//Проверяет наличие аккаунта в БД
  $this->backControl->loginResponse($_POST['login'],$_POST['password']);
}



}
 ?>
