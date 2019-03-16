<?php 
namespace Campus\Api\Card;

/**
 *  济南大学校园身份认证
 *  チーナン大学の個人識別サービス
 *  Personal Identification Service of Ujn
 *  
 *  @author Hafrans@163.com
 *  
 *  @version 0.2.0
 *  
 *  
 *  eg.
 *  $p = new CardLogin("220151214023", "201706");
 *       if($p->inflate()){
 *          echo $p;
 *      }else{
 *          echo "error";
 *      }
 */

final class CardLogin{
    
    const LOGIN_SUCCESS = 1;
    const LOGIN_FAILED  = 4;
    const LOGIN_NOT_PROCESS = 0;
    const SYSTEM_ERROR = 45;
    const SYSTEM_PARSE_ERROR = 46;
    
    private $server = "http://auth.ujn.edu.cn/TONGYI_CHECK/yktkh/checkyktkh.asp";
    private $loginstatus = LOGIN_NOT_PROCESS;
    
    
    //根据auth ujn 的参数生成的成员列表
    
    private $emailusername;//校内邮箱 
    
    private $yktkhusername;//校园卡号码
    
    private $tongyishenfen;//身份
    
    private $xm;//姓名
    
    private $bm;//部门
    
    private $bmtop;//顶级部门
    
    private $bmm;//部门编号
    
    private $errorcode = 0;//错误编码
    
    private $renzhengfangshi;//认证方式
    
    private $syskey; //系统标识符
    
    private $form = array();
    
    
     
    
    private $cardno;
    private $password;
    
    
    private $headers = array(
        "Accept"=>"text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
        "Accept-Language"=>"zh-CN,zh;q=0.8",
        "DNT"=>"1",
        "Host"=>"auth.ujn.edu.cn",
        "Origin"=>"http://ecampus.ujn.edu.cn",
        "Referer"=>"http://ecampus.ujn.edu.cn/index.asp",
        "User-Agent"=>"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.7 Safari/537.36", 
        "Cache-Control"=>"max-age=0",
        "Connection"=>"keep-alive",
        "Accept-Encoding"=>"gzip, deflate",
        "Content-Type"=>"application/x-www-form-urlencoded"
    );
    
    
    
    public function __construct($cardno,$password){
        $this->cardno = $cardno;
        $this->password = $password; 
    }
    /**
     * 填充身份信息
     * @return boolean 登陆成功则返回true 否则返回false
     */
    public function inflate(){
        $this->parse($this->networkProcess());
        if($this->errorcode == self::LOGIN_SUCCESS){
            return true;
        }
        return false;
    }
    
    public function __toString(){
           if($this->errorcode == 0){
               return NotInflated."[$this->cardno ,无名氏 , 未知]";
           }else{
               return $this->tongyishenfen."[$this->cardno ,$this->xm , $this->bmtop]";
           }
    }
    
    /**
     * 执行网络请求操作
     * @return mixed
     */
    private function networkProcess(){//:string
        
        $ch = curl_init($this->server);
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                "yktkh"=>$this->cardno,
                "password"=>$this->password,
                "Submit"=>"%E7%99%BB%E9%99%86",
                "towhere"=>"ecampus"
            )));
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
        
    }
    
    public function getError(){
        return $this->errcode;
    }
    
       
    /**
     * 变量注入
     * @param  $string
     * @throws \Exception
     */
    private function parse($string){
        
        if(mb_stripos($string,"6607网络中心")){
            $this->errcode = SYSTEM_ERROR;
            return;
        }
        //echo htmlspecialchars($string);
        $string = trim($string);
        
        if(preg_match_all('/name=errcode\svalue="(.*?)"/m', $string,$m)){
            if($m[1][0] != 0){
                $this->errorcode = CardLogin::LOGIN_FAILED;
            }else{
                $this->errorcode = CardLogin::LOGIN_SUCCESS;
                //inject infos 
                if(preg_match_all('/name=["]?(\w*?)["]?\svalue="(.*?)"/m', $string,$m)){
                    
                    foreach ($m[1] as $k => $v){
                        $this->$v = $m[2][$k];
                        $this->form[$v] = $m[2][$k];
                    }
                    
                }else{
                    throw new \Exception("认证页面传来无效信息！");
                }   
                var_dump($this);
            }
        }else{
            throw new \Exception("认证页面传来无效信息！");
        }        
        
    }
    /**
     * @return the $emailusername
     */
    public function getEmailusername()
    {
        return $this->emailusername;
    }

    /**
     * @return the $yktkhusername
     */
    public function getYktkhusername()
    {
        return $this->yktkhusername;
    }

    /**
     * @return the $tongyishenfen
     */
    public function getTongyishenfen()
    {
        return $this->tongyishenfen;
    }

    /**
     * @return the $xm
     */
    public function getXm()
    {
        return $this->xm;
    }

    /**
     * @return the $bm
     */
    public function getBm()
    {
        return $this->bm;
    }

    /**
     * @return the $bmtop
     */
    public function getBmtop()
    {
        return $this->bmtop;
    }

    /**
     * @return the $bmm
     */
    public function getBmm()
    {
        return $this->bmm;
    }

    /**
     * @return the $renzhengfangshi
     */
    public function getRenzhengfangshi()
    {
        return $this->renzhengfangshi;
    }
    
    public function getForm(){
        return $this->form;
    }
    

     
}











?>