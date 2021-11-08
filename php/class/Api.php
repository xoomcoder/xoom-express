<?php

class Api 
{
    static function now ()
    {
        echo date("Y-m-d H:i:s");
        echo "\n";
    }

    static function get_option ($params)
    {
        extract($params);

        if ($name ?? false) {
            $result = get_option($name);
        }

        echo "$result\n" ?? "\n";
    }

    static function set_option ($params)
    {
        extract($params);

        if ($name ?? false) {
            $result = update_option($name, $value ?? "");
        }

        echo "$result\n" ?? "\n";
    }

    static function unzip ($params)
    {
        extract($params);

        $target ??= realpath(__DIR__ . "/../../../");

        if (is_dir($target) && ($url ?? false)) {
            $zipcontent = file_get_contents($url);
            $tmpfile = tempnam(sys_get_temp_dir(), "xp");
            echo "$url\n$tmpfile\n$target\n";
            file_put_contents($tmpfile, $zipcontent);
            $zip = new ZipArchive;
            $res = $zip->open($tmpfile);
            if ($res === true) {
                $zip->extractTo($target);
            }
        }
    }

    static function git ($params) 
    {
        $branch = $params["branch"] ?? "main";
        $params["url"] = $params["url"] . "/archive/refs/heads/$branch.zip" ?? "";
        Api::unzip($params);
    }

    static function insert_post ($params) 
    {
        extract($params);
        $post_title = trim($post_title ?? "");

        if ($post_title) {
            $pid = wp_insert_post($params);

            echo "$pid";
    
        }
    }

    static function add_page ($params)
    {
        $params["post_status"] ??= "publish";
        $params["post_type"] ??= "page";
        Api::insert_post($params);
    }

    static function add_menu ($params)
    {
        extract($params);

        $name ??= "";
        $title ??= "";
        $url ??= "";
        $status ??= "publish";

        $id = 0;
        $menu = wp_get_nav_menu_object($name);
        if ($menu === false) {
            $id = wp_create_nav_menu($name);         
        }
        else {
            $id = $menu->term_id ?? 0;
        }
        if ($id > 0) {
            $url = esc_url_raw($url);
            $items = get_posts([
                "post_type" => "nav_menu_item",
                "meta_key" => "_menu_item_url", 
                "meta_value" => $url
            ]);
            if (empty($items)) {
                $itemid = wp_update_nav_menu_item($id, 0, [
                    'menu-item-title'  => $title,
                    'menu-item-url'    => $url,
                    'menu-item-status' => $status, 
                ]);
                echo "$itemid,$name,$title,$url,$status,$id\n";
    
            }
        }
    }

}
