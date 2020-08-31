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
class lesson extends BaseController
{
	
	//所有方案
	public function list(){

		$data = Db::table('planType') -> select();

		//$map = Db::table('lessons') ->select();

		$result = [];
		array_walk_recursive($data, function($value) use (&$result){
			array_push($result, $value);
		});

		if ($result) {

			$data = [
				'planTypes' => $result,
			];

			return $this->result(1,$data,"success");
		}else{

			return $this->result(2,null,"error");
		}
		
	}


	//新增方案
	public function addPlanType(){

		$data = [
			'id' => '',
		];

		$result = Db::table('planType') ->insert($data);

		$id = Db::table('planType') ->max("id");		
		//die;	
		$new_data = [
			"id" => $id,
			'eduId' => "",
			'endTime' => "09:30",
			'startTime' => "08:30",
			"isRollCall" => "",
			"name" => "第一节",
			"planType" => $id,
		];

		$new_result = Db::table('lessons') ->insert($new_data);

		if ($new_result) {
			
			$data = [];
			return $this->result(1,$data,"success");
		}else{

			return $this->result(2,"","error");
		}
	}


	//删除方案
	public function deleteByPlanType(){

		$id = $_GET["planType"];

		$result = Db::table('planType') ->delete($id);

		if ($result) {
			
			return $this->result(1,"","success");

		}else{

			return $this->result(2,"","error");
		}

	}


	//当前节次方案
	public function lessonInfo(){

		$planType = $_GET["id"];
	
		$result = Db::table('lessons') ->where("planType",$planType)->select();

		
		$data = [
			'map' => $result,
		];

		if ($result) {
			
			return $this->result(1,$data,"success");

		}else{

			return $this->result(2,"","error");
		}
	}


	//新增节次
	public function save(){

		$data = $_POST;

		$data["eduId"] = "";
		$data["isRollCall"] = "";

		$result = Db::table('lessons') ->insert($data);

		if ($result) {
			
			return $this->result(1,"添加成功","success");

		}else{

			return $this->result(2,"添加失败","error");
		}
	}


	//删除节次
	public function deleteById(){

		$id = $_GET["id"];

		$result = Db::table('lessons') ->where("id",$id)->delete();

		if ($result) {
			
			return $this->result(1,"删除成功","success");

		}else{

			return $this->result(2,"删除失败","error");
		}
	}



}