<?php

class Api 
{
    static function now ()
    {
        echo date("Y-m-d H:i:s");
        echo "\n";
    }

    static function get_option ($query)
    {
        if (!current_user_can('manage_options')) return "...\n";

        $params = [];
        parse_str($query, $params);
        extract($params);

        if ($name ?? false) {
            $result = get_option($name);
        }

        echo "$result\n" ?? "\n";
    }

    static function set_option ($query)
    {
        if (!current_user_can('manage_options')) return "...\n";

        $params = [];
        parse_str($query, $params);
        extract($params);

        if ($name ?? false) {
            $result = update_option($name, $value ?? "");
        }

        echo "$result\n" ?? "\n";
    }

    static function unzip ($query)
    {

        if (!current_user_can('manage_options')) return "...\n";
        $params = [];
        parse_str($query, $params);
        extract($params);

        if ($url ?? false) {
            $zipcontent = file_get_contents($url);
            $tmpfile = tempnam(sys_get_temp_dir(), "xp");
            echo "$url\n$tmpfile\n";
            file_put_contents($tmpfile, $zipcontent);
            $zip = new ZipArchive;
            $res = $zip->open($tmpfile);
            if ($res === true) {
                $zip->extractTo(realpath(__DIR__ . "/../../../"));
            }
        }
    }

}
