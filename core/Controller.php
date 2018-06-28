<?php
namespace application\core;
use application\classes\DB;

class Controller{

  protected $backControl;
  protected $params;
  protected $view;
  protected $model;
  protected $db;

  public function __construct($params,$model){
    $back='application\backController\\'.'Back'.ucfirst($params['controller']);
    $this->db=new DB();
    $this->model=$model;
    $this->params=$params; //params='controller'+'action'
    $this->backControl=new $back($this->db,$this->model);
    $this->view=new View($this->params,$this->backControl);

  }

//$model_path='application\models\\'.ucfirst($this->params['controller']).'.php';

  protected function sessionUser(){
    if(isset($_SESSION['USER']['LOGIN']) && $_SESSION['USER']['LEVEL']=='student'){
      return true;
    }else{
      return false;
    }
  }

  private function newSession($session_name,$session_value){// Создание сессии
    $_SESSION["$session_name"]=$session_value;              // с указанным именем
  }

}

?>
