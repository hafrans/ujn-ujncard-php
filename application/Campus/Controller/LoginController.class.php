<?php 
namespace Campus\Controller;

use Common\Controller\HomebaseController;


class LoginController extends HomebaseController{
    
    public function index(){
        
        $this->display(":login");
        
    }
    
    public function dologin(){
        if(IS_POST){
            if(!sp_check_verify_code()){
                $this->error("验证码错误！");
            }
            $model = M("EcardOperators");
            
            $_validate = array(
                array('operator', 'require', '账号不能为空！', 1 ),
                array('password', 'require','密码不能为空！',1),
            );
            if($model->validate($_validate)->create() === false){
                $this->error($model->getError());
            }
            
            $operator = I('post.operator',null,'htmlspecialchars');
            
            $result = $model->where("operator = '%s' ",array($operator))->find();
            
            if($result && $result['status'] === 0){
                $this->error("该用户已被管理员禁用！");
            }
            
            
            if($result && sp_compare_password(I('post.password','','htmlspecialchars'),$result['password'])){
                
                $arr = array(
                    "last_login_time" => date("Y-m-d H:i:s",time()),
                    "last_login_ip"   => get_client_ip(0,true),
                );
                
               if( !M("EcardOperators")->where("id = %d",array($result['id']))->save($arr)){
                   $this->error("系统异常");
               }
               $result = array_merge($result,$arr);
               
               session("optr",$result);
               
               $session_login_http_referer=session('login_http_referer');
               $redirect=empty($session_login_http_referer)?__ROOT__."/":$session_login_http_referer;
               session('login_http_referer','');
               
               $this->success("登录验证成功！", U('PrintCard/dashboard'));
                
            }else{
               $this->error("用户名或密码错误！");
            }
            
            
            
        }
        
         
    }
    
    
    
    
}


