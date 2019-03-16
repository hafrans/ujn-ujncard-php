<?php
namespace Common\Controller;

use Common\Controller\HomebaseController;
use Campus\Model\EcardOperatorsModel;

class OperatorbaseController extends HomebaseController{
    
	protected $user_model;
	protected $user;
	protected $userid;
	
	function _initialize() {
		parent::_initialize();
		
		$this->check_login();
		if(OperatorbaseController::is_user_login()){
			$this->userid=EcardOperatorsModel::get_current_userid();
			$this->users_model=D("Campus/EcardOperators");
			$this->user=$this->users_model->where(array("id"=>$this->userid))->find();
		}
	}
	
	
	public function check_login(){
	    $session_user=session('optr');
		if(empty($session_user)){
			$this->error('您还没有登录！',leuu('Campus/login/index',array('redirect'=>base64_encode($_SERVER['HTTP_REFERER']))));
		}
		if($session_user['status'] == 0){
		    $this->error('该用户已被禁用，请联系管理员',leuu('Campus/login/index',array('redirect'=>base64_encode($_SERVER['HTTP_REFERER']))));
		}
	}
		
	public static function is_user_login(){
	    return !empty($_SESSION['optr']);
	}
	
	
}