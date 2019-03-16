<?php


function GETCONFIG($name){
    return M('ecardConfig')->field("value")->find($name)['value'];
}

function PUTCONFIG($name,$value){
    return M('ecardConfig')->where("`name` = '".$name."'")->setField("value",$value);
}

function GETCONFIGS(){
    return M('ecardConfig')->select();
}

function generateRandomCode($rule){
   $Hex = array(
    "0","1","2","3","4","5","6","7","8","9",
    "A","B","C","D","E","F","G","H","I","J",
    "K","L","M","N","O","P","Q","R","S","T",
    "U","V","W","X","Y","Z","a","b","c","d",
    "e","f","g","h","i","j","k","l","m","n",
    "o","p","q","r","s","t","u","v","w","x",
    "y","z"
    );
    $CODE = "";
    
    for($i = 0; $i < strlen($rule); $i++){
        usleep(1);
        if($rule[$i] == '%'){
            mt_srand(intval(microtime(true)*10000));
            switch($rule[++$i]){
                case 'd':
                    $CODE .= $Hex[mt_rand(0,9)];
                    break;
                case 'A':
                    $CODE .= $Hex[mt_rand(10,35)];
                    break;
                case 'a':
                    $CODE .= $Hex[mt_rand(36,61)];
                    break;
                case 'D': 
                    $CODE .= $Hex[mt_rand(10,61)];
                    break;
                case '%':
                    $CODE .= "%";
                    break;
                case 'r':
                    $CODE .= $Hex[mt_rand(0,61)];
                    break;
                default:
                    $CODE .= $rule[$i];
                    break;
                
            }
        }else{
            $CODE .= $rule[$i];
        }
        
    }
  
    return $CODE;
    
    
}


/**
 * 检查用户登录
 */
function check_login(){
    $session_user=session('user');
    if(empty($session_user)){
        $this->error('您还没有登录！',leuu('user/login/index',array('redirect'=>base64_encode($_SERVER['HTTP_REFERER']))));
    }

}

function getNameByCardno($showcardno){
    $m =  M()->db(1,'DB_CAMPUS');
     
    $sql = "select SMT_Name,SMT_Sex,SMT_DeptCode,SMT_StatusCode from  smart.smart_personnel t where SMT_Salaryno= '$showcardno' ";
     
    $res = $m->query($sql)[0];
    $m->db(0);
    if(isset($res['smt_name'])){
        return iconv('gbk','utf-8',$res['smt_name']);
    }else{
        return false;
    }
}


/**
 * 获取打印日志handle
 * @return Model|\Think\Model
 */
function getPrintLogHandler(){
    $model = M('ecardPrintLog');
    $model->operator   = empty($_SESSION['optr']['operator'])?"未知":$_SESSION['optr']['operator'];
    $model->nickname   = empty($_SESSION['optr']['nickname'])?"未知":$_SESSION['optr']['nickname'];
    $model->ip_address = $_SERVER['REMOTE_ADDR'];
    $model->time = date("Y-m-d H:i:s",time());
    return $model;
}


/**
 * 给予用户一次的校园卡照片更换系统使用权限
 * 
 * type ==  0 仅有浏览权限
 * type ==  1 可以使用系统
 * 
 */

function grantUserPermission($showcardno,$name,$type=0,$time=100){
    session("card_user",array("cardno"=>$showcardno,"name"=>$name,"raw_photo_uri"=>getEntranceImageURIByCardno($showcardno)));
   if($type){
       if($time == 0){
           $time = GETCONFIG('MAX_CHECK_TIMES');
       }
       session("card_fc_times",$time);
       session("card_entity",M("ecardChange")->where("showcardno = '%s' ",array(I("session.card_user")['cardno']))->find());
       //     $this->redirect("PublicShow/myecardphoto");
   }
   return true;
}
/**
 * 回收权限
 * 
 */
function revokeUserPermission_1(){
    session("card_fc_times",false);
    //     $this->redirect("PublicShow/myecardphoto");
    return true;
}


function grantUserPermission_1_after_0($time = 0){
       
       if($_SESSION['card_user']){
           if($time == 0){
               $time = GETCONFIG('MAX_CHECK_TIMES');
           }
           session("card_fc_times",$time);
           session("card_entity",M("ecardChange")->where("showcardno = '%s' ",array(I("session.card_user")['cardno']))->find());
       }
}


function getUserDetails($showcardno){
    
   $m =  M()->db(1,'DB_CAMPUS');
   
   $sql = "select SMT_Name,SMT_Sex,SMT_DeptCode,SMT_StatusCode from  smart.smart_personnel t where SMT_Salaryno= '$showcardno' "; 
   
   $res = $m->query($sql)[0];
   
   if(!$res)
       return false;
   
   $arr['name'] = iconv('gbk','utf-8',$res['smt_name']);
   $arr['sex']  = iconv('gbk','utf-8',$res['smt_sex']);
   $arr['deptcode'] = $res['smt_deptcode'];
   $arr['statuscode'] = $res['smt_statuscode'];
   
   $sql = 'select SMT_DEPTNAME from smart.SMART_DEPT where smt_deptcode='.$arr['deptcode'];
   
   $res = $m->query($sql)[0];
   
   $arr['dept'] = iconv('gbk','utf-8',$res['smt_deptname']);
   
   // '00	教师
   // '04	临时人员
   // '02	学生
   // '03	研究生
   // '06	聘任制教工
   // '01	离退休教职工
   // '05	教工家属
   // '07	进修生
   // '09	毕业生
   // '10	留学生
   // '08	外籍教师
   // '定义02学生和03研究生身份为学生。00教师，06聘任制教工，08外籍教师身份为教师。
   
   $sql = "select SMT_DEPTNAME from smart.SMART_DEPT where smt_deptcode='".substr($arr['deptcode'], 0,6)."'";
   
   $res = $m->query($sql)[0];
   
   $arr['depttop'] = iconv('gbk','utf-8',$res['smt_deptname']);
   
   $sql = "select SMT_StatusName from smart.Smart_Status where SMT_StatusCode='".$arr['statuscode']."'";
   
   $res = $m->query($sql)[0];
    
    $arr['truestatusname'] = iconv('gbk','utf-8',$res['smt_statusname']);

   switch ($arr['statuscode']){
       case "00":
       case "06":
       case "08":
           $arr['statusname'] = "教师";
           break;
       case "02":
           $arr['statusname'] = "学生";
           break;
       case "03":
           $arr['statusname'] = "研究生";
           break;
   }
   
   $m->db(0);
   
   return $arr; 
    
    
}


/**
 * 从ImageData剥离BASE64图片数据
 * @param unknown $raw_data
 * @return mixed
 */
function fetchImageData($raw_data){
    return preg_replace("/data.*?base64,(.*)/m","$1", $raw_data);
}

/** 
 * 将BASE64编码图片包装成为浏览器能识别的编码
 * @param unknown $raw_data
 * @return string
 */
function imageDataWrapper($raw_data){
    return "data:image/jpeg,base64,".$raw_data;
}

/**
 * 存储图片
 * @param unknown $path
 * @param unknown $bs64edData
 * @return number
 */
function storeImage($path,$bs64edData){
    $f = function($path) use (&$f){
        if(!is_dir($path)){
            $f(dirname($path));
        }
        mkdir($path);
    };
    $f(dirname($path));
    return file_put_contents($path, base64_decode($bs64edData));
}
/*
 * 从图片获取BASE64编码图片
 */
function getImageBs64($path){
    return base64_encode(file_get_contents($path));
}
/**
 * 从校园卡卡号获取入学照片的BASE64编码
 * @param unknown $showcardno
 */
function getEntranceImageBs64ByCardno($showcardno){
    
    if(substr($showcardno, 0,2) == "02"){
        $showphoto = substr($showcardno,-10).".jpg";
    }else{
        if(substr($showcardno,0, 1) == "2"){
            $showphoto = substr($showcardno,-11).".jpg";
        }else{
            $showphoto = "nopic.jpg";
        }
    }
    
    $m =  M()->db(1,'DB_CAMPUS');
     
    $sql = "select SMT_Name,SMT_Sex,SMT_DeptCode,SMT_StatusCode from  smart.smart_personnel t where SMT_Salaryno= '$showcardno' ";
     
    $res = $m->query($sql)[0];
    
    $tostatus = $res['smt_statuscode'];
    
    $m->db(0);
    
    if($tostatus == "00" or $tostatus == "01" or $tostatus == "06" or $tostatus == "08"){
        $showphoto = "PHOTO/TEACHER/".$showcardno.".jpg";
//         $shenfen = "教师";
//         $showbumen = $deptnametop; //教师直接显示顶级部门
    }
    
    if($tostatus == "02"){
    
        $card_id_prefix = substr($showcardno,0,2);
    
        if( $card_id_prefix == "02"){
            $showphoto = "PHOTO/".substr($showcardno,2,4)."/".substr($showcardno,2,10).".jpg";
        }
    
        if($card_id_prefix == "22"){
            $showphoto = "PHOTO/".substr($showcardno,1,4)."/".substr($showcardno,1,11).".jpg";
        }
    
        $shenfen="学生";
    
//         if(substr($showcardno,2,4) >= 2009){//2009年及其之后的显示学院，之前的显示专业班
//             $showbumen = $deptnametop;
//         }else{
//             $showbumen = $deptname;
//         }
    }
    
    if($tostatus == "03"){
        $showphoto = "PHOTO/GRADUATE/".substr($showcardno,2,10).".jpg";
        $shenfen = "研究生";
//         $showbumen = $deptname; // 研究生直接填写班级
    }
    
    //echo "http://iplat.ujn.edu.cn/".$showphoto;
    return getImageBs64("http://iplat.ujn.edu.cn/".$showphoto);
}

/**
 * 从校园卡卡号获取照片URI
 * @param unknown $showcardno
 * @return string
 */
function getEntranceImageURIByCardno($showcardno){
    if(substr($showcardno, 0,2) == "02"){
        $showphoto = substr($showcardno,-10).".jpg";
    }else{
        if(substr($showcardno,0, 1) == "2"){
            $showphoto = substr($showcardno,-11).".jpg";
        }else{
            $showphoto = "nopic.jpg";
        }
    }
    
    $m =  M()->db(1,'DB_CAMPUS');
     
    $sql = "select SMT_Name,SMT_Sex,SMT_DeptCode,SMT_StatusCode from  smart.smart_personnel t where SMT_Salaryno= '$showcardno' ";
     
    $res = $m->query($sql)[0];
    
    $tostatus = $res['smt_statuscode'];
    
    $m->db(0);
    
    if($tostatus == "00" or $tostatus == "01" or $tostatus == "06" or $tostatus == "08"){
        $showphoto = "PHOTO/TEACHER/".$showcardno.".jpg";
        //         $shenfen = "教师";
        //         $showbumen = $deptnametop; //教师直接显示顶级部门
    }
    
    if($tostatus == "02"){
    
        $card_id_prefix = substr($showcardno,0,2);
    
        if( $card_id_prefix == "02"){
            $showphoto = "PHOTO/".substr($showcardno,2,4)."/".substr($showcardno,2,10).".jpg";
        }
    
        if($card_id_prefix == "22"){
            $showphoto = "PHOTO/".substr($showcardno,1,4)."/".substr($showcardno,1,11).".jpg";
        }
    
        $shenfen="学生";
    
        //         if(substr($showcardno,2,4) >= 2009){//2009年及其之后的显示学院，之前的显示专业班
        //             $showbumen = $deptnametop;
        //         }else{
        //             $showbumen = $deptname;
        //         }
    }
    
    if($tostatus == "03"){
        $showphoto = "PHOTO/GRADUATE/".substr($showcardno,2,10).".jpg";
        $shenfen = "研究生";
        //         $showbumen = $deptname; // 研究生直接填写班级
    }
    
    return "http://iplat.ujn.edu.cn/".$showphoto;
}
/**
 * 存储照片
 * @param unknown $showcardno
 * @param string $prefix
 * @return string
 */
function getImageStorePath($showcardno,$prefix=""){
    if(substr($showcardno, 0,2) == "02"){
        $showphoto = substr($showcardno,-10).".jpg";
    }else{
        if(substr($showcardno,0, 1) == "2"){
            $showphoto = substr($showcardno,-11).".jpg";
        }else{
            $showphoto = "nopic.jpg";
        }
    }
    
     $m =  M()->db(1,'DB_CAMPUS');
     
    $sql = "select SMT_Name,SMT_Sex,SMT_DeptCode,SMT_StatusCode from  smart.smart_personnel t where SMT_Salaryno= '$showcardno' ";
     
    $res = $m->query($sql)[0];
    
    $tostatus = $res['smt_statuscode'];
    
    $m->db(0);
    
    if($tostatus == "00" or $tostatus == "01" or $tostatus == "06" or $tostatus == "08"){
        $showphoto = "PHOTO/TEACHER/".$showcardno.".jpg";
        //         $shenfen = "教师";
        //         $showbumen = $deptnametop; //教师直接显示顶级部门
    }
    
    if($tostatus == "02"){
    
        $card_id_prefix = substr($showcardno,0,2);
    
        if( $card_id_prefix == "02"){
            $showphoto = "PHOTO/".substr($showcardno,2,4)."/".substr($showcardno,2,10).".jpg";
        }
    
        if($card_id_prefix == "22"){
            $showphoto = "PHOTO/".substr($showcardno,1,4)."/".substr($showcardno,1,11).".jpg";
        }
    
        $shenfen="学生";
    
        //         if(substr($showcardno,2,4) >= 2009){//2009年及其之后的显示学院，之前的显示专业班
        //             $showbumen = $deptnametop;
        //         }else{
        //             $showbumen = $deptname;
        //         }
    }
    
    if($tostatus == "03"){
        $showphoto = "PHOTO/GRADUATE/".substr($showcardno,2,10).".jpg";
        $shenfen = "研究生";
        //         $showbumen = $deptname; // 研究生直接填写班级
    }
    
    return $prefix."/".$showphoto;
}