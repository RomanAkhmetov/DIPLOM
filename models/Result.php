<?php
namespace application\models;
use application\core\Model;
use mysqli;

$mysqli=new mysqli('localhost','root','','learn_db_1');

class Result extends Model{


public $totalScore;



public function getResultsToUser(){
  if(isset($_SESSION['USER']['USER_ID'])){
    $authUserId=$_SESSION['USER']['USER_ID'];
  }
  $results=$this->db->query("SELECT TITLE,DATE,SCORE,AUTHOR,QUEST_AMOUNT FROM RESULT_HEADER INNER JOIN TEST ON TEST=TEST_ID WHERE USER=$authUserId");
  return $results;
}



//Сохранение результатов
  public function saveHeader($score,$finalTest){
   $stmt=$this->db->PDO->prepare("INSERT INTO RESULT_HEADER (TEST, USER, DATE, SCORE) VALUES (?, ?, ?, ?)");
     $stmt->bindParam(1, $test);
     $stmt->bindParam(2, $user);
     $stmt->bindParam(3, $date);
     $stmt->bindParam(4, $scoreDB);

     $test=(integer)$finalTest['TEST']['TEST_ID'];
     $user=(integer)$finalTest['USER']['USER_ID'];
     $date=$this->nowDateTime();
     $scoreDB=$score;
     $stmt->execute();
     return $this->db->PDO->lastInsertId();
  }

  public function saveResults($headerNum,$finalTest,$rightAnswers){
    $preparedAnswerArray=NULL;
    $questions=$finalTest['TEST']['QUESTIONS'];
  foreach($questions as $questNum=>$question){
    $preparedAnswerArray[$questNum]=array(
      'USER_ANSWER'=>$question['USER_ANSWER'],
      'IS_RIGHT'=>NULL
    );
  }

  foreach($preparedAnswerArray as $questNum=>$question){
    if(in_array($questNum,$rightAnswers)){
      $preparedAnswerArray[$questNum]['IS_RIGHT']=true;
    }else{
      $preparedAnswerArray[$questNum]['IS_RIGHT']=false;
    }
  }


foreach($preparedAnswerArray as $questNum=>$question){
    $stmt=$this->db->PDO->prepare("INSERT INTO RESULT (USER_ANSWER ,PARENT_HEADER ,PARENT_QUEST,IS_RIGHT) VALUES (?, ?, ?, ?)");
      $stmt->bindParam(1, $user_answer);
      $stmt->bindParam(2, $parent_header);
      $stmt->bindParam(3, $parent_quest);
      $stmt->bindParam(4, $is_right);

      $user_answer=$question['USER_ANSWER'];
      $parent_header=$headerNum;
      $parent_quest=$questNum;
      $is_right=$question['IS_RIGHT'];
      $stmt->execute();
    }

  }


//Сохраняет заголовок результата и его расшифровку
  public function save($finalTest){
    $check=$this->check($finalTest);
    $headerNum=$this->saveHeader($check['score'],$finalTest);
    $this->saveResults($headerNum,$finalTest,$check['rightAnswers']);
  }

//Возвращает текущую дату и время сервера
  public function nowDateTime(){
    $day_today = date("Y-m-d");
    $today[1] = date("H:i:s");
    return $day_today.' '.$today[1];
  }





  //=================================================================


  public function check($finalTest){
    $mysqli=new mysqli('localhost','root','','learn_db_1');
    if(mysqli_connect_errno()){
      printf("Подключение к серверу MySQL невозможно");
      exit;
    }
    $studentArray=$finalTest['TEST']['QUESTIONS'];
    $teacherArray=$this->TeacherArray($mysqli,array_keys($finalTest['TEST']['QUESTIONS']));
    return ($this->scoreCount($finalTest,$this->buildStudentResults($studentArray),$this->buildTeacherResults($teacherArray)));
  }




  public function scoreCount($finalTest,$studentResults,$teacherResults){
      $score=0;
      $wrongFieldCount=array();
      $wrongNull=array();
      $wrongFieldContain=array();
      $wrongCondition=array();
      $wrongRowCount=array();








      //Проверка по null
      foreach($teacherResults as $teacherQuestNum=>$teacherQuest){
        if($studentResults[$teacherQuestNum]==NULL){
          array_push($wrongNull,$teacherQuestNum);
          unset($studentResults[$teacherQuestNum]);
          unset($teacherResults[$teacherQuestNum]);
        }
      }




      //Проверка по количеству полей
      foreach($teacherResults as $teacherQuestNum=>$teacherQuest){
          if($studentResults[$teacherQuestNum]->field_count!=$teacherQuest->field_count){
            array_push($wrongFieldCount,$teacherQuestNum);
            unset($studentResults[$teacherQuestNum]);
            unset($teacherResults[$teacherQuestNum]);
          }
      }


      //Проверка по количеству строк
      foreach($teacherResults as $teacherQuestNum=>$teacherQuest){
          if($studentResults[$teacherQuestNum]->num_rows!=$teacherQuest->num_rows){
            array_push($wrongRowCount,$teacherQuestNum);
            unset($studentResults[$teacherQuestNum]);
            unset($teacherResults[$teacherQuestNum]);
          }
      }



    $teacherFields=array();
      //Создание массиов с полями
      foreach($teacherResults as $teacherQuestNum=>$teacherQuest){
        $row=mysqli_fetch_assoc($teacherQuest);
        $teacherFields[$teacherQuestNum]=array_keys($row) ;
      }

      foreach($studentResults as $studentQuestNum=>$studentQuest){
        if($studentQuest){
          $row=mysqli_fetch_assoc($studentQuest);
          $studentFields[$studentQuestNum]=array_keys($row);
        }else{
          $studentFields[$studentQuestNum]=NULL;
        }

      }

    //Сранение массивов выбранных полей
    $SarrayS=null;
      foreach($teacherFields as $teacherFieldNum=>$teacherField){
          $SarrayS=serialize($studentFields["$teacherFieldNum"]);
          foreach($teacherField as $field){
            $pos = strpos($SarrayS, $field);
            if($pos==false){
            array_push($wrongFieldContain,$teacherFieldNum);
            $equal=false;
          }else{
            $equal=true;
          }
          }
          if($equal){
            $qqq[$teacherFieldNum]=$studentFields["$teacherFieldNum"];
          }
      }

       $testAnswers=array_keys($finalTest['TEST']['QUESTIONS']);
       $rightAnswers=array();
      foreach($testAnswers as $key){
        if(!in_array($key,$wrongFieldCount)){
          if(!in_array($key,$wrongNull)){
            if(!in_array($key,$wrongFieldContain)){
              if(!in_array($key,$wrongRowCount)){
                array_push($rightAnswers,$key);
                $score++;
              }
            }
          }
        }
      }



      return array(
        'score'=>$score,
        'wrongFieldCount'=>$wrongFieldCount,
        'wrongNull'=>$wrongNull,
        'wrongFieldContain'=>$wrongFieldContain,
        'rightAnswers'=>$rightAnswers
      );
    }



    public function buildStudentResults($studentArray){
      $mysqli=new mysqli('localhost','root','','learn_db_1');
      foreach($studentArray as $userQuestNum=>$userQuest){

      //Костыль
        $userQuest["DB"]="learn_db_".$userQuest['DB'] ;
      //Костыль
      $mysqli->select_db($userQuest['DB']) or die ('Can\'t use foo : ' . mysql_error());
        if($userQuest['USER_ANSWER'] != '' || $userQuest['USER_ANSWER']!=NULL){


          $selfAnswerBefore=$userQuest['USER_ANSWER'];
            $deny_words = array("delete","insert","desc","description","create","update","drop",'alter');
          $selfAnswerAfter=str_replace($deny_words, "[ATTEMPT TO MODIFY]", strtolower($selfAnswerBefore), $count);


          if ($result = $mysqli->query(strtoupper($selfAnswerAfter))) {
            $studentResults["$userQuestNum"]=$result;
          }else{
            $studentResults["$userQuestNum"]=NULL;
          }
         }else{
           $studentResults["$userQuestNum"]=NULL;
         }
      }
      return $studentResults;
  }//end_function


  //Массив с вопросами для проверки
    public function TeacherArray($mysqli,$questsNums){
      $preparedTeacherArray=null;
      $questNumArray=$questsNums;
      $questNumString=implode(",", $questNumArray);
      $mysqli->select_db('system_db') or die ('Can\'t use foo : ' . mysql_error());
          if ($result = $mysqli->query("SELECT QUEST_ID, RIGHT_ANSWER,DB_TITLE FROM QUESTION INNER JOIN system_db.DATABASE ON DB=DB_ID WHERE QUEST_ID IN".'('.$questNumString.')')){
         while( $row = $result->fetch_assoc() ){
          $preparedTeacherArray[$row['QUEST_ID']]=array(
            'RIGHT_ANSWER'=>$row['RIGHT_ANSWER'],
            'DB'=>$row['DB_TITLE']
          );
         }
      }
      $mysqli=null;
      $result=null;
      return $preparedTeacherArray;
    }//end_function





  //Массив объектов результата преподавателя
  public function buildTeacherResults($teacherArray){
    $mysqli=new mysqli('localhost','root','','learn_db_1');
    foreach($teacherArray as $teacherQuestNum=>$teacherQuest){
        $mysqli->select_db($teacherQuest['DB']) or die ('Can\'t use foo : ' . mysql_error());
      $a[]=$teacherQuest['RIGHT_ANSWER'];
        if ($result = $mysqli->query($teacherQuest['RIGHT_ANSWER'])) {
          $teacherResults["$teacherQuestNum"]=$result;
        }else{
          $teacherResults["$teacherQuestNum"]=NULL;
        }
    }
    return $teacherResults;
  }//end_function







  //=================================================================

}//end_class
