<?php
/**
 * author:  Long Hai LH
 * date:    2020-09-07 15:18:11
 * licence: MIT
 */

class Express
{
    static $class_dir = "";

    static function start()
    {
        Express::$class_dir = __DIR__;
        spl_autoload_register("Express::autoload");

        Express::install();
    }

    static function autoload ($classname)
    {
        $classfile = Express::$class_dir . "/$classname.php";
        if (is_file($classfile))
            include $classfile;
    }

    static function install ()
    {
        add_filter("template_include", "Express::template_redirect");
    }

    static function template_redirect ($template)
    {
        Express::buildResponse();

        $template = "";
        return $template;

    }

    static function buildResponse ()
    {
        $now = time();
        $json = [];
        
        $json["time"] = date("Y-m-d H:i:s", $now);
        $json["uri"] = $_SERVER["REQUEST_URI"];
        $json["request"] = $_REQUEST;


        status_header(200);
        header("Content-Type: application/json");
        $response = json_encode($json);
        
        // log
        file_put_contents(__DIR__ . "/log.txt", "$response\n", FILE_APPEND);

        echo $response;
    }

    //@end
}