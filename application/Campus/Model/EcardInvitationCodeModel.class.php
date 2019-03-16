<?php 
namespace  Campus\Model;

use Think\Model;


class EcardInvitationCodeModel extends Model{
   
    /**
     * 使用邀请码
     * $code 邀请码
     * $who  记录日志
     * @return true | false
     */
   public static function useInvitationCode($code,$who){
       
       $model = M('ecardInvitationCode');
       
       $data = array(
           "used" => 1,
           "who"  => $who,
       );
       
       return $model->where("`code` = '{$code}' and `used` = 0")->save($data);
   
   }
    
}
