<?php
namespace Campus\Controller;

use Common\Controller\AdminbaseController;


class AdminIndexController extends AdminbaseController{
    
    
    public $model;
    
    
    
    
    /**
     * {@inheritDoc}
     * @see \Common\Controller\AdminbaseController::_initialize()
     */
    public function _initialize()
    {
         parent::_initialize();
         $this->model = M("ecardChange");
    }

    public function index(){
        
        $this->display();
        
        
    }
    
    public function log(){
        
        switch (I("get.type",false)){
            case "apply": //申请换照片
                
                $Wxch_indent = $this->model; // 实例化Wxch_indent对象
                //unset($_GET['type']);
                $_GET = array_merge($_GET,$_POST);//分页带条件
                
                if(I("post.showcardno",false)){
                    $count = $Wxch_indent->where("showcardno like '%s%%'",array(I("post.showcardno",false)))->count();// 查询满足要求的总记录数
                    $Page  = $this->Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
                    $list  = $Wxch_indent->where("showcardno like '%s%%'",array(I("post.showcardno",false)))->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                }else{
                    $count = $Wxch_indent->count();// 查询满足要求的总记录数
                    $Page  = $this->Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
                    $list  = $Wxch_indent->order('update_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                }
                
                
                $show  = $Page->show("Admin");// 分页显示输出
               
                
                
                $this->assign('list',$list);// 赋值数据集
                $this->assign('Page',$show);// 赋值分页输出
                $this->display("log_apply"); // 输出模板
                
                
                break;
            case "photo":
                break;
            default:
                break;                    
        }      
        
    }
    
    public function showDetails(){
        
        
        
        
    }
    
    public function apply_edit(){
        
       if(IS_POST){
          
           if(I('get.action',false) == 'submit'){
              $result = M("ecardChange")->where(array("id"=>I('post.id',false)))->setField('status',I("post.status",3));
              
           if ($result) {
    			$this->success("修改成功");
    		} else {
    			$this->error('修改失败');
    		}
               
               
           }
           
       }else{
           
           $id= I('get.id',0,'intval');
           if ($id) {
               M()->db(0);
               $result = M("ecardChange")->where(array("id"=>$id))->find();
               if ($result) {
                   
                   $this->assign("data",$result);
                   $this->display();
                   
                   
               } else {
                   $this->error('用户不存在！');
               }
           } else {
               $this->error('数据传入失败！');
           }
           
       }
       
        
        
    }
    
    
    public function apply_ban(){
        
        $id= I('get.id',0,'intval');
    	if ($id) {
    		$result = M("ecardChange")->where(array("id"=>$id))->setField('available',0);
    		if ($result) {
    			$this->success("已经禁用该用户的以后的申请！");
    		} else {
    			$this->error('用户禁用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
        
    }
    
    public function apply_cancelban(){
        
        $id= I('get.id',0,'intval');
        if ($id) {
            $result = M("ecardChange")->where(array("id"=>$id))->setField('available',1);
            if ($result) {
                $this->success("已经启用该用户的以后的申请！");
            } else {
                $this->error('用户启用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    
    }
    
    public function export(){
        
        if(!I('session.res',false)){
            $this->error("没有业务进程");
            exit;
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Cache-Control: max-age=0');
        header('Content-Disposition: attachment; filename=export_'.time().".csv");
        $handle = fopen('php://output', 'a');
        $result = session('res');
        $header = array("Code","createTime","expireTime");
        fputcsv($handle, $header);
        foreach ($result as $v){
            
        
            fputcsv($handle, $v);
        }
        
        fclose($handle);
        
        exit;
        
    }
    
    public function generateCode(){
        
        
        if(IS_POST){
           $count = I('post.count',0);
           $rule  = I('post.equation','%r%r%r%r%r%r%r%r%r%r%r%r%r%r%r%r','trim');
           $date  = I('post.expire','1970-01-01 19:23:00');
           if(!I('post.equation',false,'htmlspecialchars')){
               $this->error("请输入公式");
           }
           if($count <= 0 || $count >= 1000){
               $this->error("请输入大于0,小于1000的数量");
           }
           if(!preg_match('/^[0-9a-zA-z%]*/', $rule)){
               $this->error("您输入的公式不合法");
           }
           $dateU = new \DateTime($date);
           $dateUString = (new \DateTime($date))->format('Y-m-d H:i:s');
           $dateCString = (new \DateTime())->format('Y-m-d H:i:s');
           if(time() - $dateU->getTimestamp() > 0){
               $this->error("过期日期在此时之前！");
           }
           
           $model = M('ecardInvitationCode');
           $model->startTrans();
           
//            $result = true;
           $failed = 0;
           set_time_limit(0);
           for($i = 0 ; $i < $count ; $i++){
              
               try{
                   $model->add(array(
                       "code" => generateRandomCode($rule),
                       "create_time" => $dateCString,
                       "expire_time" => $dateUString,
                       "used"        => 0
                   ));
               }catch(\Exception $e){
                     $failed++;  
               }
           }
           
           if($failed < 8/9*$count){
               $model->commit();
               $this->error("生成成功！({$failed}) 未产生");
           }else{
               $model->rollback();
               $this->error("生成失败");
           }
           
           
        }
        
        $this->display();
        
        
    }
    
    public function invitationCode(){
        
        
        if(!I('get.action',false)){
            $Wxch_indent = M("ecardInvitationCode"); // 实例化Wxch_indent对象
            //unset($_GET['type']);
            $_GET = array_merge($_GET,$_POST);//分页带条件
            $countp = I('request.count',25);
            $condition = "";
            
            switch (I('post.type',false)){
                case "used":
                    $condition .= "used = 1";
                    break;
                case "expired":
                    $condition .= "used <> 1 and expire_time < NOW()";
                    break;
                case "available":
                    $condition .= "used <> 1 and expire_time >= NOW()";
                    break;
            }
            
            
            $count = $Wxch_indent->where($condition)->count();// 查询满足要求的总记录数
            $Page  = $this->Page($count,$countp);// 实例化分页类 传入总记录数和每页显示的记录数(25)
            $list  = $Wxch_indent->where($condition)->order('used asc,id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            
            $show  = $Page->show("Admin");// 分页显示输出
             
            $this->assign('list',$list);// 赋值数据集
            $this->assign('Page',$show);// 赋值分页输出
            $this->display('invatationCode'); // 输出模板
        }else{
            
            switch(I('get.action')){
                
                case "delete":
                    $model = M('ecardInvitationCode');
                    $model->startTrans();
                    $result = true;
                    foreach (I('post.ids',array()) as $id ){
                        $result &= $model->delete($id);
                    }
                    if($result){
                        $model->commit();
                        $this->success("删除成功");
                    }else{
                        $model->rollback();
                        $this->success("删除失败");
                    }
                    
                    
                    break;
                    
                case "export":
                    
                    $model = M('ecardInvitationCode');
                    
                    foreach (I('post.ids',array()) as $id ){
                        $result[] = $model->field("code,create_time,expire_time")->find($id);
                    }
                    if(count($result) == count(I('post.ids'))){
                       
                        session('res',$result);
                        $this->success("导出成功",U("AdminIndex/export"));
                        
                        
                    }else{
                        
                        $this->error("导出失败");
                    }
                    break;
                
            }
            
            
        }
        
        
    }
    
    public function setting(){
        
        
        if(IS_POST){
            PUTCONFIG("SITE_TITLE", I('post.SITE_TITLE'));
            PUTCONFIG("MAX_CHECK_TIMES", I('post.MAX_CHECK_TIMES'));
            PUTCONFIG("CONFIDENCE_THRESHOLD", I('post.CONFIDENCE_THRESHOLD'));
            PUTCONFIG("PHOTO_TIPS", I('post.PHOTO_TIPS'));
            PUTCONFIG("PROVISION", I('post.PROVISION'));
            PUTCONFIG('SITE_FOOTER', I('post.SITE_FOOTER'));
            
            $this->success("修改成功");
        }
        
        
        $this->assign("SITE_TITLE",M('ecardConfig')->field("value")->find('SITE_TITLE')['value']);
        $this->assign("MAX_CHECK_TIMES",M('ecardConfig')->field("value")->find('MAX_CHECK_TIMES')['value']);
        $this->assign("CONFIDENCE_THRESHOLD",M('ecardConfig')->field("value")->find('CONFIDENCE_THRESHOLD')['value']);
        $this->assign("PHOTO_TIPS",htmlspecialchars_decode(M('ecardConfig')->field("value")->find('PHOTO_TIPS')['value']));
        $this->assign("PROVISION",htmlspecialchars_decode(M('ecardConfig')->field("value")->find('PROVISION')['value']));
        $this->assign('SITE_FOOTER',htmlspecialchars_decode(GETCONFIG('SITE_FOOTER')));
        $this->display();
        
    }
    

    public function invitationLog(){
        
        if(!I('get.action',false)){
            $Wxch_indent = M("ecardInvitationCode"); // 实例化Wxch_indent对象
            //unset($_GET['type']);
            $_GET = array_merge($_GET,$_POST);//分页带条件
            $countp = I('request.count',25);
            $condition = "used = 1 ";
            
            if(I('post.start',false)&&I('post.end',false)){
                $condition .= "and used_time between '".I('post.start',false)."' and '".I('post.end',false)."'";
            }elseif(I('post.start',false)){
                $condition .= "and used_time >= '".I('post.start',false)."'";
            }elseif(I('post.end',false)){
                $condition .= "and used_time <= '".I('post.end',false)."'";
            }
            
           
        
            $count = $Wxch_indent->where($condition)->count();// 查询满足要求的总记录数
            $Page  = $this->Page($count,$countp);// 实例化分页类 传入总记录数和每页显示的记录数(25)
            $list  = $Wxch_indent->where($condition)->order('used_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
            $show  = $Page->show("Admin");// 分页显示输出
             
            $this->assign('list',$list);// 赋值数据集
            $this->assign('Page',$show);// 赋值分页输出
            $this->display(); // 输出模板
       
        }else{
            
            
            
            switch(I('get.action')){
        
                case "delete":
                    $model = M('ecardInvitationCode');
                    $model->startTrans();
                    $result = true;
                    foreach (I('post.ids',array()) as $id ){
                        $result &= $model->delete($id);
                    }
                    if($result){
                        $model->commit();
                        $this->success("删除成功");
                    }else{
                        $model->rollback();
                        $this->success("删除失败");
                    }
        
        
                    break;
        
                case "export":
        
                    $model = M('ecardInvitationCode');
        
                    foreach (I('post.ids',array()) as $id ){
                        $result[] = $model->field("code,create_time,expire_time")->find($id);
                    }
                    if(count($result) == count(I('post.ids'))){
                         
                        session('res',$result);
                        $this->success("导出成功",U("AdminIndex/export"));
        
        
                    }else{
        
                        $this->error("导出失败");
                    }
                    break;
        
            }
            
            
            
 
        
    }
    }
    
    
    
    
    
    
}