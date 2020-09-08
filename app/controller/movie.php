<?php
namespace app\controller;
use app\BaseController;
use think\facade\Db;
use think\respoonse\Json;
use think\Request;
include '../autoloader.php';
use think\facade\Config;
use config\status;
use core\requests;
use core\selector;
class movie extends BaseController{


	public function getData(){
		$snoopy = new \Snoopy();
		$url = 'https://www.imdb.com/title/tt3672840/?ref_=fn_tt_tt_1';
	   //  $snoopy->agent = "Mozilla/5.0 (Linux; U; Android 8.1.0; zh-CN; Redmi 6 Pro Build/OPM1.171019.019) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0; Chrome/57.0.2987.108 UCBrowser/12.5.5.1035 Mobile Safari/537.36";
	   //  $snoopy ->referer="https://m.ting22.com/ting/1547-180.html";
	   $data = $snoopy ->fetch($url); 
	   // $snoopy->fetchform();  //获取表单
	   // echo $snoopy->results;
		$webpage = mb_convert_encoding($data,"UTF-8","GBK");

	}


	
	//获取电影首页列表
	public function getList(){

		$data = Db::table('movie')
		->alias('m')
		->join('movie_status s','m.id = s.m_id and s.type in (1,2,3) and s.status = 1')->select();

		if($data){

			$nowData = [];
			$soonData = [];
			$topData = [];
			$startsArr = [];

			// $startsArr = $this->starts($data[0]['grade']);
			// dump($startsArr);
			// exit;

			$count = count($data);
			for ($i=0; $i < $count; $i++) { 
				if($data[$i]['type'] == 1){
					array_push($nowData, $data[$i]);
				}
				if ($data[$i]['type'] == 2) {
					array_push($soonData, $data[$i]);
				}
				if($data[$i]['type'] == 3){
					array_push($topData, $data[$i]);	
				}
			}


			$new_data = [

				'0' => $nowData,
				'1'=> $soonData,
				'2' => $topData
			];

			return $this->result(1,$new_data,"success");

		}else{

			return $this->result(2,$data,"error");
		}


	}



	//获取更多电影
	public function more_movie(){

		$param = request()->param();

		
		$type = $param['type'];
		$currentPage = $param['currentPage'];
		$pageSize = $param['pageSize'];


		$data = Db::table('movie')
		->alias('m')
		->join('movie_status s','m.id = s.m_id and s.type = 1 and s.status = 0')->limit($pageSize * ($currentPage - 1),$pageSize)->select();

		if ($data) {
			return	$this->result(1,$data,'success');
		}else{
			return	$this->result(2,$data,'error');
		}

			
	}


	//搜索
	public function find_Movie(){
		$param = request()->param();

		$type = $param['type'];
		$keyWord = $param['keyWord'];

		// $type = 1;
		// $keyWord = '刘镇伟';

		$data = Db::query("SELECT
			* 
		FROM
			movie 
		WHERE
			id IN (
			SELECT
				MIN(m.id) 
			FROM
				movie m
				INNER JOIN movie_status s ON m.id = s.m_id 
				AND s.type = ? 
				AND s.STATUS = 0 
				AND stars LIKE '%".$keyWord."%'
				OR director LIKE '%".$keyWord."%'
				OR title LIKE '%".$keyWord."%'	
			GROUP BY title 
			)",[$type]);

		// dump($data);

		if($data){
			return $this->result(1,$data,'success');
		}else{
			return $this->result(2,$data,'error');
		}
	}

	
}