<?php 
namespace app\controller;
use app\BaseController;
use think\facade\Db;
use think\respoonse\Json;
use think\Request;
use config\status;


/**
 * 
 */
class user extends BaseController
{
	

	//用户列表
	public function userList(){


		$result = Db::table('user') ->select();
		//return Json($data);
		 
		
		if($result){

			$data = [
				'lists' => $result,
			];

			return $this->result(1,$data,"success");

		}else{

			return $this->result(2,'',"error");
		}

		
	}
}

