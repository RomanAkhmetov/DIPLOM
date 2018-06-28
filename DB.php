<?php
namespace application\classes;
use PDO;

class DB{

  public $PDO;

  public function __construct(){
    $config=require 'application/settings/config/databaseConfig.php';
	  $this->PDO=new PDO('mysql:host='.$config['host'].';dbname='.$config['database'].'',$config['user'],$config['password']);
  }



  public function query($query){
  $result=$this->PDO->query($query);
  $fetch_res=$result->fetchAll(PDO::FETCH_ASSOC);
       if(is_array($fetch_res)){
         return $fetch_res;
       }else{
         return null;
       }
  }



  public function testConnection(){
    if(isset($this->db)){
      echo 'Connection succes';
    }
  }

}

 ?>
