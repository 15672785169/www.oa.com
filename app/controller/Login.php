<?php
namespace app\controller;
use app\Basecontroller;

/**
 *  
 */
class Login extends Basecontroller
{
	
	public function login(){
		return $this->result(1,"成功","success");
	}
}


