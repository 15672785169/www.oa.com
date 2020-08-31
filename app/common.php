<?php
namespace common;
use think\respoonse\Json;
// 应用公共文件

function show($status, $msg = "error", $data = [], $httpStatus = 200){

	$result = [
		"status" =>$status,
		"msg" => $msg,
		"result" => $data
	];

	return Json($result, $httpStatus);
}