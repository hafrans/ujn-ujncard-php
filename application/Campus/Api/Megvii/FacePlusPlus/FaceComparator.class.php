<?php
namespace Campus\Api\Megvii\FacePlusPlus;

class FaceComparator {
    
    private $driverLocation = "main.py";
    
    public function __construct(){
        if(func_num_args() != 0){
            $this->driverLocation = func_get_arg(0);
        }
    }
    
    public function compare($face1 , $face2){//:float
                
        
        $url = "https://api-cn.faceplusplus.com/facepp/v3/compare";
        
        $field =  array(
            "api_key"=>C("API_KEY"),
            "api_secret"=>C("API_SEC"),
            "image_base64_1" => $face1,
            "image_base64_2" => $face2,
        );
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if(!($result = curl_exec($ch))){
            return false;
        }

        curl_close($ch);
        
        return json_decode($result,true);
        
        
    }
    
    
}