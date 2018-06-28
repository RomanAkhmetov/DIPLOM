<?php
namespace application\backController;
use application\classes\Session;
use application\models\Result;
use mysqli;
class BackTest{
  public $db;
  public $model;


  public function __construct($db,$model){
     $this->db=$db;
     $this->model=$model;
  }

//Загрузка теста
  public function loadTestBody($id){
    $loadedTest=$this->model->getTestData($id);
    $_SESSION['TEST']=array(
        'TEST_BIND'=>$_SESSION['USER']['UID'],
        'TEST_ID'=>$loadedTest[0]['TEST_ID'],
        'TITLE'=>$loadedTest[0]['TITLE'],
        'DESCRIPTION'=>$loadedTest[0]['DESCRIPTION'],
        'AUTHOR'=>$loadedTest[0]['AUTHOR'],
        'QUEST_AMOUNT'=>$loadedTest[0]['QUEST_AMOUNT'],
        'QUESTIONS'=>$this->loadTestQuestions($id),
        'SELECTED'=>NULL
    );

    return $_SESSION['TEST'];
  }

  public function loadTestQuestions($id){
    $questions=$this->model->getQuestions($id);
    return $questions;
  }

  //Загрузка теста в модель
  public function loadToModel(){
    $this->model->workTest=$_SESSION['TEST'];
  }




//Процесс тестирования
public function backProcessTest($operation,$id=null,$answer=null){


  switch ($operation) {
      case 'change':
      $text=$_SESSION['TEST']['QUESTIONS'][$id]['TEXT'];
      $returnAnswer=$_SESSION['TEST']['QUESTIONS'][$id]['USER_ANSWER'];
      $dbName=$_SESSION['TEST']['QUESTIONS'][$id]['DB_NAME'];
      $dbTitle=$_SESSION['TEST']['QUESTIONS'][$id]['DB'];
      $response=array('operation'=>$operation,'id'=>$id,'text'=>$text,'returnAnswer'=>$returnAnswer,'dbName'=>$dbName,'dbTitle'=>$dbTitle);
      $_SESSION['TEST']['SELECTED']=$id;
      echo json_encode($response);
          break;


      case 'select':
      $id=$_SESSION['TEST']['SELECTED'];

      $_SESSION['TEST']['QUESTIONS'][$id]['USER_ANSWER']=$answer;
      $returnAnswer=$_SESSION['TEST']['QUESTIONS'][$id]['USER_ANSWER'];
      $response=array('operation'=>$operation,'id'=>$id,'end'=>$this->testEnd(),'returnAnswer'=>$returnAnswer);
          echo json_encode($response);
          break;




      case 'save':
        $finalTest['TEST']=$_SESSION['TEST'];
        $finalTest['USER']=$_SESSION['USER'];
        unset($_SESSION['TEST']);
        $checkProcess=new Result();
        $response=array('operation'=>$operation,'canredirect'=>true,'final'=>$checkProcess->save($finalTest));
        echo json_encode($response);
        break;
  }
}


//Определяет окончание тестирования
  public function testEnd(){
    $test=$_SESSION['TEST']['QUESTIONS'];
    foreach($test as $questId=>$quest){
        if($quest['USER_ANSWER']==NULL || $quest['USER_ANSWER']==''){
          return false;
        }else{
          return true;
        }
    }
  }



  //Процесс тестирования
  public function backProcessLearn($operation,$userAnswer=null,$systemAnswer=null,$db=null){


    switch ($operation) {

        case 'select':

        $mysqli=new mysqli('localhost','root','',$db);

$arrays=array();



        $selfAnswerBefore=$userAnswer;
        $deny_words = array("delete","insert","desc","description","create","update","drop",'alter');
        $selfAnswerAfter=str_replace($deny_words, "[ATTEMPT TO MODIFY]", strtolower($selfAnswerBefore), $count);

$userQuery = $mysqli->query(strtolower($selfAnswerAfter));
array_push($arrays,$userQuery);
        if($userQuery){
          while($row = $userQuery->fetch_assoc()){
           $userResult[]=$row;
          }
        }

        if($count>0){
          $userResult=array(
            'error'=>'error_modify'
          );
        }



$systemQuery = $mysqli->query($systemAnswer);
array_push($arrays,$systemQuery);
        if($systemQuery){
          while($row = $systemQuery->fetch_assoc()){
           $systemResult[]=$row;
          }
        }


   //$this->scoreCount($userQuery,$systemQuery);

        $response=array('operation'=>$operation,'userResult'=>$userResult,'systemResult'=>$systemResult,'db'=>$db,'arrays'=>$arrays);
        echo json_encode($response);
        break;



        case 'loadQuest':

        $mysqli=new mysqli('localhost','root','','system_db');
        $result = $mysqli->query("SELECT *FROM LEARN_QUESTION");

                while($row = $result->fetch_assoc()){
                 $preparedTeacherArray[$row['QUEST_ID']]=array(
                   'QUEST_ID'=>$row['QUEST_ID'],
                   'TEXT'=>$row['QUEST_TEXT'],
                   'RIGHT_ANSWER'=>$row['RIGHT_ANSWER'],
                   'DB'=>"learn_db_".$row['DB']
                 );
                }
        $randomQuest=$preparedTeacherArray[array_rand($preparedTeacherArray,1)];

        $response=array('operation'=>$operation,'question'=>$randomQuest);
        echo json_encode($response);
        break;
      }
  }


  public function scoreCount($userResult,$systemResult){
      //$score=0;
      //$wrongFieldCount=array();
      // $wrongNull=array();
      // $wrongFieldContain=array();
      // $wrongCondition=array();
      // $wrongRowCount=array();









    //
    //   //Проверка по null
    //   foreach($teacherResults as $teacherQuestNum=>$teacherQuest){
    //     if($studentResults[$teacherQuestNum]==NULL){
    //       array_push($wrongNull,$teacherQuestNum);
    //       unset($studentResults[$teacherQuestNum]);
    //       unset($teacherResults[$teacherQuestNum]);
    //     }
    //   }
    //
    //
    //
    //
    //   //Проверка по количеству полей
    //   foreach($teacherResults as $teacherQuestNum=>$teacherQuest){
    //       if($studentResults[$teacherQuestNum]->field_count!=$teacherQuest->field_count){
    //         array_push($wrongFieldCount,$teacherQuestNum);
    //         unset($studentResults[$teacherQuestNum]);
    //         unset($teacherResults[$teacherQuestNum]);
    //       }
    //   }
    //
    //
    //   //Проверка по количеству строк
    //   foreach($teacherResults as $teacherQuestNum=>$teacherQuest){
    //       if($studentResults[$teacherQuestNum]->num_rows!=$teacherQuest->num_rows){
    //         array_push($wrongRowCount,$teacherQuestNum);
    //         unset($studentResults[$teacherQuestNum]);
    //         unset($teacherResults[$teacherQuestNum]);
    //       }
    //   }
    //
    //
    //
    // $teacherFields=array();
    //   //Создание массиов с полями
    //   foreach($teacherResults as $teacherQuestNum=>$teacherQuest){
    //     $row=mysqli_fetch_assoc($teacherQuest);
    //     $teacherFields[$teacherQuestNum]=array_keys($row) ;
    //   }
    //
    //   foreach($studentResults as $studentQuestNum=>$studentQuest){
    //     if($studentQuest){
    //       $row=mysqli_fetch_assoc($studentQuest);
    //       $studentFields[$studentQuestNum]=array_keys($row);
    //     }else{
    //       $studentFields[$studentQuestNum]=NULL;
    //     }
    //
    //   }
    //
    // //Сранение массивов выбранных полей
    // $SarrayS=null;
    //   foreach($teacherFields as $teacherFieldNum=>$teacherField){
    //       $SarrayS=serialize($studentFields["$teacherFieldNum"]);
    //       foreach($teacherField as $field){
    //         $pos = strpos($SarrayS, $field);
    //         if($pos==false){
    //         array_push($wrongFieldContain,$teacherFieldNum);
    //         $equal=false;
    //       }else{
    //         $equal=true;
    //       }
    //       }
    //       if($equal){
    //         $qqq[$teacherFieldNum]=$studentFields["$teacherFieldNum"];
    //       }
    //   }
    //
    //    $testAnswers=array_keys($finalTest['TEST']['QUESTIONS']);
    //    $rightAnswers=array();
    //   foreach($testAnswers as $key){
    //     if(!in_array($key,$wrongFieldCount)){
    //       if(!in_array($key,$wrongNull)){
    //         if(!in_array($key,$wrongFieldContain)){
    //           if(!in_array($key,$wrongRowCount)){
    //             array_push($rightAnswers,$key);
    //             $score++;
    //           }
    //         }
    //       }
    //     }
    //   }
    //


      return array(
        'systemResult'=>$userResult,
        'userResult'=>$systemResult
      );
    }







}//end_class
 ?>
