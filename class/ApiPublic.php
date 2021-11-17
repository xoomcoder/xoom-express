<?php

class ApiPublic 
{

    static function mail ($form)
    {
        extract($form);
        $key ??= "";
        $result = "";

        print_r($form);
        $apiKey = V::get("apiKey");
        if ($key == md5($apiKey)) {
            $to ??= "";
            $subject ??= "";
            $message ??= "";
            $additional_params ??= [];
    
            $message = wordwrap($message, 70, "\r\n");
    
            $result = mail($to, $subject, $message, $additional_params);
    
        }
    }

}