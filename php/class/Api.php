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

}
