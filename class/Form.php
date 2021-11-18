<?php

class Form
{
    static function uploadZip ($zip, $input_name="", $zipath="", $json=[])
    {
        if (!empty($_FILES)) {
            $uploadInfo = $_FILES[$input_name] ?? [];
            if (!empty($uploadInfo)) {
                extract($uploadInfo);
                if ($error == 0) {
                    if ($zipath != "") {
                        $name = $zipath;    // force a path in zip file
                    }
                    extract(pathinfo($name));
                    $dirname ??= "";
                    $filename ??= "upload";
                    $extension ??= "";
    
                    // debug
                    echo "($zipath)";

                    $dirname = strtolower(preg_replace(",[^a-zA-Z0-9/],", "-", $dirname));
                    $filename = strtolower(preg_replace("/[^a-zA-Z0-9]/", "-", $filename));
                    $extension = strtolower(preg_replace("/[^a-zA-Z0-9]/", "-", $extension));
                    if ($extension != "") {
                        $zip->addFile($tmp_name, "$dirname/$filename.$extension");
                        $json["result"] = "$dirname/$filename.$extension";
                        V::set("upload/$input_name", "$dirname/$filename.$extension");    
    
                    }    
                }
            }
        }
        return $json;
    }

}