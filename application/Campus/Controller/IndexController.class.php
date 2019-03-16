<?php
namespace Campus\Controller;

use Common\Controller\HomebaseController;
use Campus\Model\EcardChangeModel;
use Campus\Model\EcardOperatorsModel;

class IndexController extends HomebaseController{
    
    
    
    private $s;
    /**
     * {@inheritDoc}
     * @see \Common\Controller\HomebaseController::_initialize()
     */
    public function _initialize()
    {
      
       
        
    }

    public function index(){
        
       if(EcardOperatorsModel::is_user_login()){
           $this->redirect("PrintCard/dashboard");
       }else{
           if(sp_is_mobile()){
               if(IS_POST){
                   if(($name = getNameByCardno(I('post.c',0))) !== false){
                       grantUserPermission($_POST['c'],$name,0);
                       $this->redirect("PublicShow/myecardphoto");
                   }else{
                       exit("校园卡号不存在");
                   }
               }else{
                   $this->display(":debug");
               }
           }else{
               $this->redirect("Login/index");
           }
       }
        
    }
    
    public function toChange(){
        grantUserPermission("220151214023", "吕中华",0,3);
        $this->redirect("PublicShow/myecardphoto");
    }
    
    public function provision(){
        if(session("isConfirmed")){
            
            
            
        }else{
            $this->display();
        }
        
        
    }
    
    
    
}