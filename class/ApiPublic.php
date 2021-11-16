<?php

class ApiPublic 
{

    static function mail ($form)
    {
        extract($form);
        $key ??= "";
        $result = "";

        if ($key == md5(V::get("apiKey"))) {
            $to ??= "";
            $subject ??= "";
            $message ??= "";
            $additional_params ??= [];
    
            $message = wordwrap($message, 70, "\r\n");
    
            $result = mail($to, $subject, $message, $additional_params);
    
        }
    }

}