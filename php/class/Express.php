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

        if (is_admin()) {
            Express::install_admin();
        }
    }

    static function install_admin ()
    {
        add_action('admin_menu', 'Express::add_menu');
                
    }

    static function add_menu ()
    {
        add_menu_page(
            'Express',
            'Express',
            'manage_options',
            'express-admin',
            'Express::build_admin',
            ''
        );

    }
    
    static function build_admin() {
        echo 
        <<<x
        <style>
        #appx textarea {
            display:block;
            width: calc(100% - 1rem);
            padding: 1rem;
            font-family:monospace;
        }
        </style>
        <h1>Express</h1>
        <div id="appx">
            <textarea v-model="command" cols="80" rows="10"></textarea>
            <h6>{{ command.length }}</h6>
            <button @click="counter++">click: {{ counter }}</button>
        </div>

        <script type="module">
        import * as Vue from 'https://cdn.jsdelivr.net/npm/vue@3.2.21/dist/vue.esm-browser.prod.js';
        
        const appxconfig = {
            data() {
              return {
                command: '',
                counter: 0,
              }
            }
          }
          
        window.appx = Vue.createApp(appxconfig).mount('#appx');

        </script>
        x;
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