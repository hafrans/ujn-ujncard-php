<?php 

    $arr = ["APPle","BAnana"];
    
    try{
        echo $str =  base64_encode(@openssl_encrypt(serialize($arr),"AES-128-CFB","hafrans",OPENSSL_RAW_DATA));
    }catch (Exception $e){
        echo $e->getMessage();
    }
    var_dump(unserialize(@openssl_decrypt(base64_decode($str), "AES-128-CFB", "hafrans",OPENSSL_RAW_DATA)));


?>