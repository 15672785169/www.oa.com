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
class room extends BaseController
{
	
	//场室列表
	public function roomList(){

		$data = Db::table('room') ->select();
		//return Json($data);
		$arr = [
			'list' => $data
		]; 
		
		return $this->result(1,$arr,"success");
		
	}


	//新增场室
	public function addRoom(){

		if(!empty($_POST["id"])){

		}else{

			$data = $_POST;

			$data["createTime"] = date('y-m-d h:i:s',time());

			$result = Db::table('room')->insert($data);

			if ($result == 1) {
				return $this->result(1,"保存成功","success");
			}else{
				return $this->result(2,"失败","error");
			}

		}
		
	}


	//删除场室
	public function delRoom(){

		if (empty($_POST["id"])) {
			return $this->result(2,"请求参数错误","error");
		}else{
			$id =$_POST["id"];

			$result = Db::table('room') ->where("id",$id)->delete();

			if ($result == 1) {
				return $this->result(1,"删除成功","success");
			}else{
				return $this->result(2,"删除失败","error");
			}
		}
	}


	//编辑场室
	public function editRoom(){

	}


	//查询场室信息
	public function findRoomInfo(){
		$id = $_POST["id"];

		$result = Db::table('room') -> where("id",$id) ->find();		//场室信息

		$chargeId = $result["roomKeeperUserId"];
		//dump($result);
		//die;
		$name = Db::table('charge') ->where("id",$chargeId) -> find();
		//dump($name);
		$roomTypes = Db::table('roomType') ->select();						//场室类型

		$result["realName"] = $name["name"];

		$data = [
			'entity' => $result,
			'roomTypes' => $roomTypes,
		];

		if (!empty($result)) {

			return $this->result(1,$data,"success");

		}else{

			return $this->result(2,"请求失败","error");
		}
	}


	//场室负责人和审核人
	public function chargeList(){

		$chargeData = Db::table('charge') -> select();
		$auditData = Db::table('auditora') -> select();

		$arr = [
			'chargeData' => $chargeData,
			'auditData' => $auditData
		];

		return $this->result(1,$arr,"success");
	}

	//场室类型
	public function roomType(){

		$typeData = Db::table('roomtype') -> select();

		if($typeData){

			$arr = [
				'types' => $typeData,
			];

			return $this->result(1,$arr,"success");

		}else{

			return $this->result(2,'',"error");
		}
		
	}


	//方案
	public function getPlanType(){

		$data = Db::table('plantype') ->select();


		$result = [];
		array_walk_recursive($data, function($value) use (&$result){
			array_push($result, $value);
		});

		//dump($result);
		return $this->result(1,$result,"success");
	}


	//节次安排
	public function lessons(){

		$data = Request()->param();
		$id =$data['plantype'];
		file_put_contents("api.txt", $id,FILE_APPEND);


		$lessons = Db::table('lessons') ->where('plantype', $id) ->select();
		//dump($lessons);
		$arr = [
			'lessons' => $lessons,
		];

		return $this->result(1,$arr,"success");
	}


	//图片上传
    public function uploadImg(){

    	if ($_FILES["file"]["error"] > 0) {
    		return $this->result(2);
    	}else{
    		//获取数组里面的值 
			$name = $_FILES["file"]["name"];			//上传文件的文件名 
			$type = $_FILES["file"]["type"];			//上传文件的类型 
			$size = $_FILES["file"]["size"];			//上传文件的大小 
			$tmp_name = $_FILES["file"]["tmp_name"];	//上传文件的临时存放路径 
			$error  = $_FILES["file"]["error"];			//上传后系统返回的值 
			//判断是否为图片 
			 switch ($type){ 
			 	case 'image/pjpeg':$okType=true; 
			   	break; 
			 	case 'image/jpeg':$okType=true; 
			    break; 
			    case 'image/gif':$okType=true; 
			    break; 
			    case 'image/png':$okType=true; 
			    break; 
			}

			if($okType){
					/** 
				   * 0:文件上传成功<br/> 
				   * 1：超过了文件大小，在php.ini文件中设置<br/> 
				   * 2：超过了文件的大小MAX_FILE_SIZE选项指定的值<br/> 
				   * 3：文件只有部分被上传<br/> 
				   * 4：没有文件被上传<br/> 
				   * 5：上传文件大小为0 
				   */
				move_uploaded_file($_FILES["file"]["tmp_name"],
    			$destination = "Uploads/" . $_FILES["file"]["name"]);	

    			$data = [
    				'imgPath' => $destination,
    			];

				return $this->result(1, $data, "success");
			}else{
				$msgStr = "请上传jpg,gif,png等格式的图片！";
				$data = [];
				return $this->result(2, $data, $msgStr);

			}
    			
    	}
        
    }


    //我的场室预约
    public function listMyRoomReservation(){

    	//用户id
    	//$userId = $_POST["userId"];

    	$result = DB::table('myroomList') ->select();
    	// $result = DB::table('myroomList') ->where('userId',$userId) ->select();

    	if ($result) {
    		
    		$data = [
    			'list' => $result,
    		];

    		return $this->result(1,$data,"success");

    	}else{

    		return $this->result(2,"","error");
    	}
    }


    //选择预约时间接口

    public function listReservationTable(){
    	
    	//dump(count($_POST));
    	if (count($_POST) > 2) {
    		
	    	$weekNumber = $_POST["weekNumber"] ? $_POST["weekNumber"] : "";
	    	$oldWeekStartStr = $_POST["oldWeekStartStr"];

	    	if ($weekNumber == -1) {

    			$new_day   = strtotime($oldWeekStartStr);
    			$weekStart = date('y-m-d',($new_day - 24 * 3600 * 7));         

    			//获取上周
    			$date = $this->lastweek($oldWeekStartStr);


    		}else{

    			$new_day   = strtotime($oldWeekStartStr);
    			$weekStart = date('y-m-d',($new_day + 24 * 3600 * 7));          

    			//获取下周
    			$date = $this->nextweek($oldWeekStartStr);

    		}

    	}else{

    		$weekStart = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 2) * 24 * 3600));		//周一

    		$date = $this->weekday();
    	}
    	//dump($date);
    	
    	$planType = $_POST["planType"];
	    $roomId   = $_POST["roomId"];

    	$planTypeData = Db::table('lessons') ->where("planType",$planType) ->select();

    	$selData = [
    		'id' => $roomId,
    		'planTypeId' => $planType,
    	];

    	$times = Db::table('room') -> where($selData) -> value('times');

    	$new_times = explode(',', $times);

    	$today = date('Y-m-d');				//今天

    	$y = date('y',time());

		$m = date('m',time());

		$month = '20'.$y . '年' .$m .'月';
    	
 	  	// dump($data);

    	if ($planTypeData) {
    		

    		$data = [
    			'lessons' => $planTypeData,
    			'month' => $month,
    			'mapList' => [],
    			'thisWeek' =>"日期",
    			'times' => $new_times,
    			'today' => $today,
    			'weekDates' => $date,
    			'weekStart' => $weekStart,
    		];

    		return $this->result(1,$data,"success");

    	}else{

    		return $this->result(2,"","error");
    	}
    }


    //预约场室接口
    public function saveRoomReservation(){
    	// $da = array($_POST["lesson"]);
    	$date = $_POST["date"];
    	$week = $_POST["week"];
    	$lessonId = $_POST["lessonId"];
    	for ($i=0; $i <count($lessonId) ; $i++) { 
    		# code...
    	}
    	// dump($date);

    	$arr = [];

    	$arr = Db::table();

    	// $time = date('y-m-d h:i:s',time());
    	// $data = [
    	// 	'id' => "",
    	// 	"roomId" => $_POST["roomId"],
    	// 	"planType" => $_POST["planType"],
    	// 	"auditorState" => 0,
    	// 	"name" => $_POST["name"],
    	// 	"mark" => $_POST["mark"],
    	// 	"periodName" => $_POST["periodName"],
    	// 	"typeName" => $_POST["typeName"],
    	// 	"createTime" => $time,
    	// ];

    	// Db::startTrans(); 		// 启动事务

    	// $result = Db::table('myroomList') ->insert($data);

    	// Db::rollback();

    	// $id = Db::table('myroomList') -> max("id");

    	// $da = array($_POST["lesson"]);

    	// $lesson = '第'.$_POST["lesson"].'节';

    	// $week 	= '星期'.$_POST["week"];

    	// $detailData = [

    	// 	'id' => $id,
    	// 	'userId' => '',
    	// 	'auditorId' => '',
    	// 	'auditorName' => '',
    	// 	'auditorState' => 0,
    	// 	'auditorDate' => '',
    	// 	'departments' => '',
    	// 	'location' => '',
    	// 	'name' => '',
    	// 	'peopleNumber' => $_POST["peopleNumber"],
    	// 	'periodName' => '',
    	// 	'roomKeeperUserName' => '',
    	// 	'userPhone' => '',
    	// 	'mark' => $_POST["mark"],
    	// 	'createTime' => $time,
    	// ];
    }


    //bus
    public function bus(){

    	$result = Db::table('schoolbus') ->select();

    	$trains = Db::table('busstation') ->select();


    	dump($result);


    }


    //车次
    public function trainList(){

    	//$id = $_GET["id"];

    	$result = Db::table('busInfo') ->select();

    	return $this->result(1,$result,"success");
    }	

}

