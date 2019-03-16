<?php
namespace Campus\Controller;

use Common\Controller\HomebaseController;
use Campus\Model\EcardChangeModel;
use Campus\Model\EcardInvitationCodeModel;


class PublicShowController extends HomebaseController{
    
    
    public function myecardphoto(){
        //TODO 更换成当前用户的cardno
        if(!($puser = I("session.card_user",false))){
            $this->error("用户信息获取失败！");
            return;
        }
        
         $res =  M("ecardChange")->where("showcardno = '%s' ",array(I("session.card_user")['cardno']))->find();
         if($res){
             session("card_entity",$res);
         }
         switch ($res['status']){
             case EcardChangeModel::STATUS_PENDING:
                 $status = "等待当面审核";
                 break;
             case EcardChangeModel::STATUS_OK;
                 $status = "通过审核";
                 break;
             case EcardChangeModel::STATUS_FAILED:
                 $status = "审核失败";
                 break;
             default:
                 $status = "未知状态";
                 break;
         }
         
         $this->assign("rawPhoto",$puser['raw_photo_uri']);
         $this->assign("uploadedPhoto",getImageStorePath($puser['cardno'],"data/campus"));
         $this->assign("card",$res);
         $this->assign("cardno",$puser['cardno']);
         $this->assign("card_fc_time",$s = session("card_fc_times"));
         $this->assign("status",$status);
         $this->assign("available",$puser['available']);
         
         
         $this->assign("title","校园卡照片更换系统");
         $this->display();
        
        
    }
    
    public function provision(){
        
        if(I("get.action","nope","htmlspecialchars") == "accept"){
            session("isConfirmed",true);
            if(I("get.redirect_uri",false)){
                echo "<script>window.location.href='http://".$_SERVER['SERVER_NAME'].urldecode(htmlspecialchars_decode($_GET['redirect_uri']))."'</script>";
                exit;
            }
        }
        
        $this->assign("title","校园卡照片更换系统");
        $this->display();
        
        
    }
    
    public function infopage(){
        $this->assign("title","校园卡照片更换系统");
        $this->display();
    }
    
    public function invitation(){
        
        
        
        
        if(IS_POST){
            $arr = array(
                "status" => false,
                "check"  => false,
            );
            if(!I('session.invi_check',false) || time() - I('session.invi_check')['last_time'] > 60 ){
                session("invi_check",array("times"=>0,"last_time"=>time()));
            }
            
            if(I('session.invi_check')['times'] <= 6){
                $_SESSION['invi_check']['times'] =   $_SESSION['invi_check']['times'] +1;
                $user  = $_SESSION['card_user']['name'];
                $cardno= $_SESSION['card_user']['cardno'];
                $arr['status'] = true;
                if(EcardInvitationCodeModel::useInvitationCode(trim(I('post.code',"@#$","htmlspecialchars")), "用户 {$user}({$cardno})通过U码使用了校园卡照片更换系统")){
                   
                    $arr['check']  = true;
                    
                    grantUserPermission_1_after_0(GETCONFIG('MAX_CHECK_TIMES'));
                    
                }
                
            }
            
            $this->ajaxReturn($arr);
            
            
        }
        
        $this->assign("title","邀请码输入");
        $this->display();
        
        
    }
    
    public function endSession(){
        session_destroy();
    }
    
    
    
    
}