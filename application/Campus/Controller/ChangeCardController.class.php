<?php
namespace Campus\Controller;

use Common\Controller\HomebaseController;
use Campus\Api\Megvii\FacePlusPlus\FaceComparator;
use Campus\Model\EcardChangeModel;


class ChangeCardController extends HomebaseController{
    
    private $model;
    
    /**
     * {@inheritDoc}
     * @see \Common\Controller\HomebaseController::_initialize()
     */
    public function _initialize()
    {  
        
        if(!($puser = I("session.card_user",false))){
            $this->error("用户信息获取失败！");
            return;
        }
        
        $entity = I('session.card_entity',null);
        
        $this->model = M("ecardChange");
        
        if(isset($entity)){
            session("isConfirmed",true);
            $this->model->where("showcardno = '%s' ",array(I("session.card_user")['cardno']))->find();
        
            if($this->model->available === 0){
                $this->assign("title","申请已被禁用");
                $this->display("ChangeCard/info_banned");
                exit;
            }
        
        }
        
        /**
         * 
         * 系统准许使用说明：
         * 
         * 扣费后，可以在用户会话上增加 3 次 人脸识别机会，如果没有机会则不可以使用
         * 
         * $_SESSION['card_fc_times'] = 3;//准许使用三次 ，三次之后，失去使用系统的机会。
         * 
         * @var \Campus\Controller\ChangeCardController $model
         */   
        if(!isset($_SESSION['card_fc_times'])){
            $this->redirect("PublicShow/invitation",array("redirect_uri"=>$_SERVER['REQUEST_URI'],"type"=>"changecard"));
        }
        
        if($_SESSION['card_fc_times'] === false){
            $this->assign("title","权限不足");
            $this->display("ChangeCard/info_warning");
            exit;
        }
        
        if($_SESSION['card_fc_times'] === 0){
            $this->assign("title","人脸识别次数已经使用完");
            $this->display("ChangeCard/info_face");
            exit;
        }
        
        
        if(!I("session.isConfirmed",false)){ //如果没有同意协议
            $this->redirect("PublicShow/provision",array("redirect_uri"=>$_SERVER['REQUEST_URI']));
        }
    }

    public function index(){
        
        echo "OK";
        
        
    }
    
    public function upload(){
        
        if(IS_POST){
            $raw_data = $_POST['img'] == null ? "" : str_replace(" ", "+", urldecode($_POST['img']));
//             $ajax_return;
            
            if(strlen($raw_data) > 16){
                session("imgsrc",$raw_data);
                $this->ajaxReturn(array("status"=>"ok"));
                //注入合法数据
                session("fc_times",3);
                session("fc_status",false);
            }else{
                $this->ajaxReturn(array("status"=>"not_ok","msg"=>"数据异常"));
            }
        }
        
        //$this->assign("title","校园卡照片更换系统");
        $this->display();
    
    
    }
    
    public function facecheck(){
        
        if(!I("session.imgsrc",false)){
            $this->error("照片数据丢失！");
        }
        
        if(IS_POST){

            $arr = array(
                "check"=>false,
                "niai" => "0.0",
                "goukaku"=>false,
                "process"=>"-1"
            );
            
            
            
            switch(I("post.cmd","no")){
                case "check":
                    session("fc_status",false);
                    $arr['process'] = 1;
                    if(I("session.card_fc_times") < 0){
                        break;
                    }
                    $comp = new FaceComparator();
                    $res = $comp->compare(fetchImageData(I("session.imgsrc")), getEntranceImageBs64ByCardno(I("session.card_user")['cardno']));
                    
                    $trytimes = 10;
                    while($res && isset($res['error_message']) && $trytimes-- ){
                        $res = $comp->compare(fetchImageData(I("session.imgsrc")), getEntranceImageBs64ByCardno(I("session.card_user")['cardno']));
                        if(!isset($res['error_message']) || !$res){
                            break;
                        }
                        
                    }
                    
                    if($res || !isset($res['error_message'])){
                        session("card_fc_times",I("session.card_fc_times")-1);
                        $arr['check'] = true;
                        if(isset($res['error_message'])){
                            $arr['msg'] = $res['error_message'];
                        }
                    }
                    
                    if($res && isset($res['confidence'])){
                        
                        $arr['niai'] = $res['confidence'];
                        $arr['face0'] = $res['faces1'];
                        $arr['face1'] = $res['faces2'];
                        
                        if($res['confidence'] > GETCONFIG("CONFIDENCE_THRESHOLD")){
                            $arr['goukaku'] = true;
                            session("fc_status",true);
                            session("fc_confidence",$res['confidence']);
                        }
                        
                    }else{
                        $arr['niai'] = -240;
                    }
                    
                    break;
                case "upload":
                    
                    $arr['process'] = 2;
                    $arr['backup'] = I("session.fc_status");
                    I("session.fc_status") && storeImage(getImageStorePath(I("session.card_user")['cardno'],"data/Campus"), fetchImageData(I("session.imgsrc"))) && $arr['check'] =true;                   
                    if($arr['check']){
                        
                        if(($ety = I("session.card_entity",false)) != false){//原本就存在的
                            $this->model->times = $this->model->times+1;
                            $this->model->status = EcardChangeModel::STATUS_PENDING;
                            $this->model->confidence = I('session.fc_confidence',0);
                            $this->model->update_time= date("Y-m-d H:i:s",time());
                           // $this->model->path = getImageStorePath(I("session.card_user")['cardno'],"data/Campus");
                            if(!$this->model->save()){
                                $arr['check'] =false;
//                                 $arr['d'] = $this->model->getDBError();
                            }
                        }else{//新增
                            $this->model->showcardno = I("session.card_user")['cardno'];
                            $this->model->name       = I("session.card_user")['name'];
                            $this->model->path       = getImageStorePath(I("session.card_user")['cardno'],"data/Campus");
                            $this->model->times      = 1;
                            $this->model->confidence = I('session.fc_confidence',0);
                            $this->model->create_time= date("Y-m-d H:i:s",time());
                            $this->model->update_time= date("Y-m-d H:i:s",time());
                            
                            if(!$this->model->add()){
                                $arr['check'] =false;
                            }
                        }
                        
                        if($arr['check']){
                            //成功之后要收回权限
                            revokeUserPermission_1();
                        
                        }
                        
                        
                    }
                    
                    break;
                default:
                    break;
            }
            $this->ajaxReturn($arr);
            
            
        }
        $this->assign("title","校园卡照片更换系统");
        $this->assign("card_fc_time",session("card_fc_times"));
        $this->assign("rawPhoto",I("session.card_user")['raw_photo_uri']);
        //$this->assign("title","校园卡照片更换系统");
        $this->display();
        
        
    }
    
    
    
    
    
    
    
    
    
}