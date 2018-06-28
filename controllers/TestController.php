<?php
namespace application\controllers;
use application\core\Controller;

class TestController extends Controller{

  public function infoAction(){

//=======old
    if($this->sessionUser()){

      if(isset($_SESSION['TEST']['QUESTIONS'])){
         $keys=array_keys($_SESSION['TEST']['QUESTIONS']);
         $_SESSION['TEST']['SELECTED']=$keys[0];
         $this->view->redirect('/test/primary');
      }else{



        if($_SERVER['REQUEST_METHOD']=='GET' && isset($_GET['id'])){
          $test_id=$_GET['id'];
          $testHeader=$this->db->query("SELECT *FROM TEST WHERE TEST_ID=$test_id");
          if(count($testHeader)==0){
            $this->view->redirect('/application/views/error/404.php');
            exit();
          }
          $this->view->render('Информация',$testHeader);
        }else{
          $this->view->redirect('/account/info');
        }
}
        }else{
      $this->view->redirect('/account/login');
      }
//=======old
  }



  public function learnAction(){
    if($this->sessionUser()){

        $_SESSION['TEST']='LEARN_MODE';
        $this->view->render('Обучение');


    //  if(isset($_SESSION['TEST']) && !isset($_SESSION['TEST']['QUESTIONS'])){

  //    }

    }else{
    $this->view->redirect('/account/login');
    }
  }






public function selectQuestAction(){
  if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
          if($_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest'){
            die('Доступ закрыт');
          }
  }
   foreach($_SESSION['TEST']['QUESTIONS'] as $questId=>$text){
     $questList[]=array(

       'text'=>$text['TEXT'], //Заголовки вопросов
       'id'=>$questId, //Номера вопросов
       'db'=>$text['DB'], //База данных
       'answer'=>$text['USER_ANSWER'],//Пользовательский ответ
       'db_name'=>$text['DB_NAME'],
       'db_desc'=>$text['DB_DESC']
     );



    // $questList['SELECTED']=$_SESSION['TEST']['SELECTED'];

   }

   $selected=$_SESSION['TEST']['SELECTED'];

   $response=array(
     'questList'=>$questList,
     'selected'=>$selected
   );

 echo json_encode($response);
}

//Универсальный метод процесса тестирования
public function frontProcessTestAction(){
$useranswer=null;
$operation=null;
$id=null;

  $operation=$_POST['operation'];

  if(isset($_POST['id'])){
    $id=$_POST['id'];
  }

  if(isset($_POST['useranswer'])){
    $useranswer=$_POST['useranswer'];
  }

  $this->backControl->backProcessTest($operation,$id,$useranswer);
}




public function frontProcessLearnAction(){
  $operation=$_POST['operation'];

  if(isset($_POST['useranswer'])){
    $userAnswer=$_POST['useranswer'];
  }else{
      $userAnswer=null;
  }

  if(isset($_POST['systemanswer'])){
    $systemAnswer=$_POST['systemanswer'];
  }else{
      $systemAnswer=null;
  }

  if(isset($_POST['db'])){
    $db=$_POST['db'];
  }else{
      $db=null;
  }



  $this->backControl->backProcessLearn($operation,$userAnswer,$systemAnswer,$db);
}




  public function primaryAction(){
if($this->sessionUser()){

    if(!isset($_SESSION['TEST']['QUESTIONS'])){
      if(isset($_SESSION['TEST']['TEST_ID']) && !isset($_SESSION['TEST']['QUESTIONS'])){
        $this->backControl->loadTestBody($_SESSION['TEST']['TEST_ID']);
        $this->backControl->loadToModel();
        $this->view->render('Тестирование');
      }else{
        $this->view->redirect('/account/info');
      }

    }else{
      $keys=array_keys($_SESSION['TEST']['QUESTIONS']);
      $this->view->render('Тестирование');
    }

    }else{
    $this->view->redirect('/account/login');
    }
  }



  public function sandboxAction(){
    if($this->sessionUser()){
    $this->view->render('Песочница');
    }else{
    $this->view->redirect('/account/login');
    }
  }







}//end class
 ?>
