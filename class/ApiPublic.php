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

    static function upload ($form)
    {
        extract($form);
        $key ??= "";
        $filename ??= "upload";
        $zipath ??= "";
        $datadir = V::get("datadir");
        if ("" != $key) {
            $zipsearch = "$datadir/data*-$key.zip";
            $files = glob($zipsearch);
            if (!empty($files)) {
                $zipfile = $files[0];
                $zip = new ZipArchive;
                $ok = $zip->open($zipfile);
                if ($ok === true) {
                    Form::uploadZip($zip, $filename, $zipath);
                    $zip->close();
                }

            }
        }
    }

}