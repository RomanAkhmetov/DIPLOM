<?php
namespace application\core;
use application\classes\DB;
class Model{

  protected $db;

  public function __construct(){
      $this->db=new DB();
  }
}
 ?>
