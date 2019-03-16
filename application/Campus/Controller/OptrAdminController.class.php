<?php
namespace Campus\Controller;

use Common\Controller\AdminbaseController;


class OptrAdminController extends AdminbaseController{
    
    
    public $model;
    
    
    /**
     * {@inheritDoc}
     * @see \Common\Controller\AdminbaseController::_initialize()
     */
    public function _initialize()
    {
         parent::_initialize();
         $this->model = M("ecardOperators");
    }

    public function index(){
        
        $this->display();
        
    }
    
    public function log(){
        
                
                $Wxch_indent = M("ecardPrintLog"); // 实例化Wxch_indent对象
                //unset($_GET['type']);
                $_GET = array_merge($_GET,$_POST);//分页带条件
                
                $condition = "";
                
                if(I('post.operation',false)){
                    $condition .= "`operation` like '%".I('post.operation',false)."%'";
                }
                
                if(I('post.start',false)&&I('post.end',false)){
                    $condition .= " time between '".I('post.start',false)."' and '".I('post.end',false)."'";
                }elseif(I('post.start',false)){
                    $condition .= " time >= '".I('post.start',false)."'";
                }elseif(I('post.end',false)){
                    $condition .= " time <= '".I('post.end',false)."'";
                }
                
                
                $count = $Wxch_indent->where($condition)->count();// 查询满足要求的总记录数
                $Page  = $this->Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
                $list  = $Wxch_indent->where($condition)->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                
                $show  = $Page->show("Admin");// 分页显示输出
               
                $this->assign('list',$list);// 赋值数据集
                $this->assign('Page',$show);// 赋值分页输出
                $this->display("log"); // 输出模板
               
        
    }
    
    public function delLog(){
        $id = I('get.id',0,'intval');
        
        if ($id) {
            $result = M("ecardPrintLog")->where(array("id"=>$id))->delete();
            if ($result) {
                $this->success("已经成功删除了该记录", $_SERVER['HTTP_REFERER']);
            } else {
                $this->error('无法删除该记录！');
            }
        } else {
            $this->error('数据传入失败！');
        }
        
    }
    
    public function showDetails(){
        
        
        
        
    }
    //操作员增加
    public function add(){
        
        if(IS_POST){
            
            $model = D("Campus/EcardOperators");
            
           if(!$model->create()){
               $this->error($model->getError());
           }
           
           $model->password = sp_password($model->password);
           $model->create_time = date("Y-m-d H:i:s",time());           
           if(!$model->add()){
               if(!empty($model->getError())){
                   $this->error($model->getError());
               }else{
                   $this->error($model->getDBError());
               }
           }else{
               $this->success("操作员创建成功！");
           }
           
        }
        
        
        $this->display();
    }
    
    //操作员列表
    public function lists(){
        
        
                $Wxch_indent = D("Campus/EcardOperators"); // 实例化Wxch_indent对象
                //unset($_GET['type']);
                $_GET = array_merge($_GET,$_POST);//分页带条件
                
                $condition = "";
                
                if(I('request.operator',false)){
                    $condition .= "`operator` like '%".I('request.operator',false)."%'";
                }
                
                if(I('request.operator',false) && I('request.nickname',false)){
                    $condition .= "or";
                }
                if(I('request.nickname',false)){
                    $condition .= "`nickname` like '%".I('request.nickname',false)."%'";
                }
                
                $count = $Wxch_indent->where($condition)->count();// 查询满足要求的总记录数
                $Page  = $this->Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
                $list  = $Wxch_indent->where($condition)->order('last_login_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                
                $show  = $Page->show("Admin");// 分页显示输出
               
                $this->assign('users',$list);// 赋值数据集
                $this->assign('Page',$show);// 赋值分页输出
                $this->display("list"); // 输出模板
        
    }
    
    public function edit(){
        
        if(IS_POST){
            $model = D("Campus/EcardOperators");
            
            if($model->create()){
                if(isset($model->password)){
                    $model->password = sp_password($model->password);
                }
                if($model->save()){
                    $this->success("修改成功！",U("OptrAdmin/lists"));
                }else{
                    $this->error($model->getDBError());
                }
            }else{
                $this->error($model->getError());
            }
            
        }
        
        $id= I('get.id',0,'intval');
    	if ($id) {
    		$result = M("EcardOperators")->where(array("id"=>$id))->find();
    		if ($result) {
    			$this->assign("operator",$result['operator']);
    			$this->assign("nickname",$result['nickname']);
    			$this->assign("id",$result['id']);
    		} 
    	} else {
    		$this->error('数据传入失败！');
    	}
        $this->display();
        
    }
    
    
    
    public function ban(){
        
        $id= I('get.id',0,'intval');
    	if ($id) {
    		$result = M("EcardOperators")->where(array("id"=>$id))->setField('status',0);
    		if ($result) {
    			$this->success("已经禁用该用户！");
    		} else {
    			$this->error('用户禁用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
        
    }
    
    public function cancelban(){
        
        $id= I('get.id',0,'intval');
        if ($id) {
            $result = M("EcardOperators")->where(array("id"=>$id))->setField('status',1);
            if ($result) {
                $this->success("已经启用该用户！");
            } else {
                $this->error('用户启用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    
    }
    
    public function del(){
        $id= I('get.id',0,'intval');
        if ($id) {
            $result = M("EcardOperators")->delete($id);
            if ($result) {
                $this->success("已经删除了该用户！");
            } else {
                $this->error('用户删除失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }
    
    
    public function invatationCode(){
        
    }
    
    
    
    
}