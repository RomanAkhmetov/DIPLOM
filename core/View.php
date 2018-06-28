<?php
namespace application\core;

class View{
    public $view;
    public $template='template';
    protected $params;


    public function __construct($params,$backControl=null){
      $this->params=$params;
      $this->backControl=$backControl;
      $this->view=$this->params['controller'].'/'.$this->params['action'];
    }

    public function render($title,$data=null,$res=null){
      ob_start();
      require_once 'application/views/'.$this->view.'.php';
      $content=ob_get_clean();
      require_once 'application/views/templates/'.$this->params['action'].'_'.$this->template.'.php';

    }

    public  function redirect($url){
		header('Location: '.$url);
		exit;
	}


}
?>
