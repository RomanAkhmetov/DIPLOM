<?php
namespace application\models;
use application\core\Model;

class Test extends Model{

  public $workTest;



  public function getTestsList(){
      $testHeader=$this->db->query("SELECT *FROM TEST");
      return $testHeader;
  }


//Загрузка теста
  public function getTestData($id){
    $test=$this->db->query("SELECT *FROM TEST WHERE TEST_ID=$id");
    if(isset($test) && count($test)!=0){
      return $test;
    }else{
      return null;
    }

  }




//Загрузка вопросов
  public function getQuestions($id){
    $questions=$this->db->query("SELECT QUEST_ID,DB_DESC,QUEST_TEXT,DB, DB_NAME FROM QUESTION INNER JOIN `DATABASE` ON DB=DB_ID WHERE PARENT_TEST=$id");
     if(count($questions)!=0){
       $preparedQuest=$this->prepareQuestions($questions);
       return $preparedQuest;
     }else{
       return null;
     }

  }
  //Подготовка вопросов
  public function prepareQuestions($questions){
    foreach($questions as $key=>$value){
       $prepareQuestion[$value['QUEST_ID']]=array(
         'TEXT'=>$value['QUEST_TEXT'],
         'USER_ANSWER'=>null,
         'DB'=>$value['DB'],
         'DB_NAME'=>$value['DB_NAME'],
         'DB_DESC'=>$value['DB_DESC']

       );
     }
    return  $prepareQuestion;
  }



}//end_class
 ?>
