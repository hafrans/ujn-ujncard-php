<?php
namespace Campus\Controller;

use Common\Controller\HomebaseController;
use Campus\Model\EcardChangeModel;
use Common\Controller\OperatorbaseController;

class PrintCardController extends OperatorbaseController{
    /**
     * {@inheritDoc}
     * @see \Common\Controller\HomebaseController::_initialize()
     */
    public function _initialize()
    {
        parent::_initialize();        
    }
    
    public function logout(){
        session_destroy();
        $this->success("登出成功！",U("Campus/Login/index"));
        
    }
    
    public function changepassword(){
        if(IS_POST){
            $arr = array(
                "status"=>false
            );
            $password = I('post.pass','default','htmlspecialchars');
            
            if(strlen($password) > 16 || strlen($password) <= 3){
                $this->ajaxReturn($arr);
            }
            
            if(M('ecardOperators')->where(' id = %s ',array($_SESSION['optr']['id']))->setField('password',sp_password($password)) ){
                $arr['status'] = true;
            }
            
            $this->ajaxReturn($arr);
            
        }
    }
    
    public function profile(){
        $this->display();
    }
    
    public function printcard(){
        
        if(($res = I('session.cur_print',false)) == false){
            exit("CAN NOT PRINT WITHOUT INFO");
        }
        $cur_print = I('session.cur_print');
        if(I('get.position','n') == 'r' && I('session.cur_print')['count']==2 && I('session.cur_print')['apply_status'] != EcardChangeModel::STATUS_FAILED){
            $res['photo'] = $res['ph2'];
            if( I('session.cur_print')['apply_status'] == EcardChangeModel::STATUS_PENDING){
                $model = M('ecardChange')->where("showcardno = '%s' ",array(I('session.cur_print')['showcardno']));
                $model->status = EcardChangeModel::STATUS_OK;
                $handler = getPrintLogHandler();
                $handler->count = 1;
                $handler->operation = "通过了用户{$cur_print['name']}({$cur_print['showcardno']})的照片更换申请";
                
                
                M()->startTrans();
                if(!$model->save() || !$handler->add()){
                    M()->rollback();
                     exit ($model->getError());
                }
                M()->commit();
            }
            
            
        }else if(I('get.position','n') == '1'  && I('session.cur_print')['count']==2){
            $res['photo'] = $res['ph1'];
            if( I('session.cur_print')['apply_status'] == EcardChangeModel::STATUS_PENDING){
                $model = M('ecardChange')->where("showcardno = '%s' ",array(I('session.cur_print')['showcardno']));
                $model->status = EcardChangeModel::STATUS_FAILED;
                
                $handler = getPrintLogHandler();
                $handler->count = 1;
                $handler->operation = "驳回了用户{$cur_print['name']}({$cur_print['showcardno']})的照片更换申请";
                M()->startTrans();
                if(!$model->save() || !$handler->add()){
                    M()->rollback();
                     exit ($model->getError());
                }
                M()->commit();
            }
        }else{
            $res['photo'] = $res['ph1'];
        }
        
        
        
        $this->assign("bundle",$res);
        $this->display();
        exit;
    }
    
    public function dashboard(){
        
        $this->display();
        
    }
    
    public function workload(){
        $operator = I('session.optr')['operator'];
        $arr = array(
            "status"=>"failed",
            "count"=>0
        );
        if($operator){
            
            
           $result = M('ecardPrintLog')->where("operator = '%s'",array($operator))->limit(5)->order('time desc')->select(); 
           
           if(count($result) > 0){
               $arr['status'] = "ok";
               $arr['count'] = count($result);
               $arr['load']  = $result;
               $this->ajaxReturn($arr);
           }else{
               $this->ajaxReturn($arr);
           }
           
           
            
            
        }else{
            $this->error("NO ID");
        }
        
        
    }
    
    public function selectphoto(){
        
        $arr = array(
              "status" => false,
              "msg"    => "unknown",
              "count"  => 0,
              "ph1"    => "are you A script kiddie?",
              "ph2"    => '(WTF)->{where\'s the flag}',
        );
        
        if(IS_POST){
            $id = trim(I("post.showcardno",false,"htmlspecialchars"));
            if($id && preg_match('/^\d{11,12}$/',$id)){
               $res =  M("ecardChange")->where("showcardno = '%s' and available = 1",array($id))->find();
               
               if(($details = getUserDetails($id)) != false){
                   $arr['status'] = true;
               }
               $arr = array_merge($arr,$details);
               $arr['count'] = 1;
               if($res){
                   $arr['count'] = 2;
                   $arr['ph2']   = $res['path'];
                   $arr['confidence'] = $res['confidence'];
                   $arr['apply_status'] = (int)$res['status']; 
                   $arr['apply_time'] = $res['update_time'];
               }
               $arr['ph1'] = getEntranceImageURIByCardno($id);
               $arr['showcardno'] = $id;
               session('cur_print',$arr);
               
            }else{
                $arr['msg'] = "invalid cardno";
                $arr['cardno'] = $id;
            }
            
            $this->ajaxReturn($arr);
            
        }
        
    }
    
    /**
     * 准许打印
     */
    public function printResponse(){
        
        if(IS_POST){
            
            $model = getPrintLogHandler();
            $model->count = I('post.count','0','intval');
            $cur_print = I('session.cur_print');
            
            
                if(I('post.pos','L') == 'r'){
                    $model->operation = "使用用户{$cur_print['name']}({$cur_print['showcardno']})申请的照片打印校园卡";
                    
                }else{
                    $model->operation = "使用用户{$cur_print['name']}({$cur_print['showcardno']})原始照片打印校园卡";
                }
            
            
            if($model->add()){
                $this->ajaxReturn(array("status"=>true));
            }else{
                $this->ajaxReturn(array("status"=>false));
            }
            
        }
        
    }
    
    public function test(){
    
        var_dump(getUserDetails("220151214086"));
    
    
    }

    
    
    
    
}