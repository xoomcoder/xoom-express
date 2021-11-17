<?php

class ApiPublic 
{

    static function mail ($form)
    {
        extract($form);
        $key ??= "";
        $result = "";

        $apiKey = V::get("apiKey");
        $md5Key = md5($apiKey);
        if ($key == $md5Key) {
            print_r($form);

            $to ??= "";
            $subject ??= "";
            $message ??= "";
            $headers ??= [];
    
            $message = wordwrap($message, 70, "\r\n");
    
            $result = wp_mail($to, $subject, $message, $headers);
    
        }
    }

}