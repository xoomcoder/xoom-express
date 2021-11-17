<?php

class Form
{
    static function uploadZip ($zip, $input_name="", $json=[])
    {
        if (!empty($_FILES)) {
            $uploadInfo = $_FILES[$input_name] ?? [];
            if (!empty($uploadInfo)) {
                extract($uploadInfo);
                if ($error == 0) {
                    extract(pathinfo($name));
                    $filename ??= "upload";
                    $extension ??= "";
    
                    $filename = strtolower(preg_replace("/[^a-zA-Z0-9]/", "-", $filename));
                    $extension = strtolower(preg_replace("/[^a-zA-Z0-9]/", "-", $extension));
                    if ($extension != "") {
                        $zip->addFile($tmp_name, "$filename.$extension");
                        $json["result"] = "$filename.$extension";
                        V::set("upload/$input_name", "$filename.$extension");    
    
                    }    
                }
            }
        }
        return $json;
    }

}