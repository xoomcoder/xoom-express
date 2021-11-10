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

        $target ??= dirname(__DIR__);

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

            echo "$pid,$post_title\n";
    
        }
    }

    static function add_page ($params)
    {
        $found = get_page_by_title(trim($params["post_title"] ?? ""));
        if (empty($found)) {
            $params["post_status"] ??= "publish";
            $params["post_type"] ??= "page";
            Api::insert_post($params);    
        }
    }

    static function set_front_page ($params)
    {
        $found = get_page_by_title(trim($params["post_title"] ?? ""));
        if ($found) {
            update_option("show_on_front", 'page');
            update_option("page_on_front", $found->ID);
        }
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
            // FIXME: add also menu check on menu item
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

    static function script ($params)
    {
        extract($params);
        $url ??= "";
        if ($url) {
            $code = file_get_contents($url);
            if ($code) {
                Express::run_command($code);
            }
        }
    }

    static function add_maintenance ($params)
    {
        $expressdir = dirname(__DIR__);
        $mydir = "$expressdir/my";
        if (!is_dir($mydir) {
            echo " create $mydir\n";

            mkdir($mydir);
            touch("$mydir/index.php");
        }

        $maintenance = [
            "template" => "",
        ]
        file_put_contents(
            "$mydir/maintenance.json", 
            json_encode($maintenance, JSON_PRETTY_PRINT));
        
    }
}
