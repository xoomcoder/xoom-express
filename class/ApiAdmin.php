<?php

class ApiAdmin 
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

        $target ??= dirname(dirname(__DIR__));

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

    static function add_zip ($params)
    {
        extract($params);
        $target ??= dirname(dirname(__DIR__));
        $zipdir = "$target/xp-data";
        if (!is_dir($zipdir)) {
            mkdir($zipdir);
            touch("$zipdir/index.php");
        }
        if (is_dir($zipdir)) {
            $name ??= "data-"md5(password_hash(uniqid(), PASSWORD_DEFAULT));
            $zip = new ZipArchive;
            $ok = $zip->open("$zipdir/$name.zip", ZipArchive::CREATE);
            if ($ok === true) {
                // empty zip is not valid
                $now = time();
                $index = [
                    "creation"  => date("Y-m-d H:i:s", $now),
                    "timestamp" => $now,
                ];
                $zip->addFromString('index.json', json_encode($index, JSON_PRETTY_PRINT));
                $zip->close();

            }
        }
    }

    static function git ($params) 
    {
        $branch = $params["branch"] ?? "main";
        $params["url"] = $params["url"] . "/archive/refs/heads/$branch.zip" ?? "";
        ApiAdmin::unzip($params);
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
            ApiAdmin::insert_post($params);    
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

    static function set_post_page ($params)
    {
        $found = get_page_by_title(trim($params["post_title"] ?? ""));
        if ($found) {
            update_option("page_for_posts", $found->ID);
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
        extract($params);
        $mode ??= "";

        $expressdir = dirname(__DIR__);
        $mydir = "$expressdir/my";
        if (!is_dir($mydir)) {
            echo "create $mydir\n";

            mkdir($mydir);
            touch("$mydir/index.php");
        }

        $maintenance_file = "$mydir/maintenance.json";
        if ($mode == "off") {
            echo "delete $maintenance_file\n";

            if (is_file($maintenance_file)) {
                unlink($maintenance_file);
            }
        }
        else {
            echo "create $maintenance_file\n";

            $maintenance = [
                "template" => "",
            ];
            file_put_contents(
                $maintenance_file, 
                json_encode($maintenance, JSON_PRETTY_PRINT));    
        }
        
    }

    static function add_plugin ($params)
    {
        extract($params);
        $name ??= "";
        $name = sanitize_file_name(basename($name));
        if ($name) {
            
            $plugindir = WP_PLUGIN_DIR . "/$name";
            if (!is_dir($plugindir)) {
                mkdir($plugindir);
                $now = date("Y-m-d H:i:s");

                $code =
                <<<x
                <?php
                /*
                
                Plugin Name: $name
                Creation: $now

                */

                x;
                file_put_contents("$plugindir/index.php", $code);
            }
        }
        else {
            echo "missing name ($name)";
        }
    }

    static function add_theme ($params)
    {
        extract($params);
        $name ??= "";
        $name = sanitize_file_name(basename($name));
        if ($name) {
            
            $themedir = get_theme_root() . "/$name";
            if (!is_dir($themedir)) {
                mkdir($themedir);
                $now = date("Y-m-d H:i:s");

                $code =
                <<<x
                /*
                
                Theme Name: $name
                Creation: $now

                */

                x;
                file_put_contents("$themedir/style.css", $code);

                file_put_contents("$themedir/index.php", "<?php ");
                file_put_contents("$themedir/functions.php", "<?php ");

            }
        }
        else {
            echo "missing name ($name)";
        }
    }

}
