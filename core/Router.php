<?php
namespace application\core;

class Router{

	protected $routes;//Массив всех существующих маршрутов
	protected $params;//Параметры все существующих маршрутов(Controller+action)



	//Конструктор формирует массив маршрутов
	public function __construct(){
		$arr=require 'application/settings/config/routes.php';
		foreach($arr as $key=>$value){
			$this->add($key,$value);
		}
	}



//Добавляет маршруты
	public function add($route,$params){
		$route='#'.$route.'#';//Делаем из роута регулярку
		//$route='#'.$route.'#';//Делаем из роута регулярку
		$route=str_replace('/','\/',$route);//Экранируем слеш
		$this->routes[$route]=$params;//Заполняем поле класса
	}




	public function match(){//Проверка наличия и корректности роута
		$url=trim($_SERVER['REQUEST_URI'],'/');
		foreach($this->routes as $route=>$params){
			if(preg_match($route,$url,$matches)){
				$this->params=$params;//Если роут найден в URL, то записываем параметры
				return true;
			}
		}
	}


	public function run(){//Запуск маршрутизации, создание экземпляра класса и вызов экшена


		// if(!isset($_SESSION['USER']['LOGIN'])){
		// 	$this->params['controller']='account';
		// 	$this->params['action']='login';
		// }

		 if($this->match()){
			 $class_path='application\controllers\\'.ucfirst($this->params['controller']).'Controller.php';
			 $class='application\controllers\\'.ucfirst($this->params['controller']).'Controller';
			 $model_path='application\models\\'.ucfirst($this->params['controller']).'.php';
       $model='application\models\\'.ucfirst($this->params['controller']);
				 if(file_exists($class_path)){
					 if(class_exists($class)){
						 if(class_exists($model)){
							 $model=new $model();
						 }
						 $controller=new $class($this->params,$model);//Создаётся объект нужного контроллера
						 $action=$this->params['action'].'Action';//Создаётся нужный метод
						 $controller->$action();//Вызывается созданный метод
             //Создаётся объект модели
					 }
				 }






		 }else{
			 Router::redirect('/application/views/error/404.php');
		 }




	}




	public static function redirect($url){
		header('Location: '.$url);
		exit;
	}

	public function error404(){

  }


}
?>
