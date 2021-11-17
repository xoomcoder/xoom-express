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
        add_plugins_page(
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
        #appx textarea, #appx pre {
            display:block;
            width: calc(100% - 1rem);
            padding: 1rem;
            font-family:monospace;
        }
        #appx label {
            display:inline-block;
            padding:0.5rem 0.5rem;
            margin: 0 auto;
        }
        .xpPopup {
            position: fixed;
            top:0;
            left:0;
            margin:0;
            width:100%;
            height:100%;
            z-index:9999;
            background-color: rgba(0,0,0,0.8);
            padding: 2rem;
            text-align:center;
        }
        .xpPopup pre {
            background-color: #ffffff;
            margin: 0 auto;
            max-width: calc(100% - 10rem);
        }
        </style>
        <h1>Express</h1>
        <div id="appx">
            <section>
                <button @click="addTerminal">Add terminal</button>
                <label v-for="(show, index) in shows">
                    <input type="checkbox" v-model="shows[index]">{{ titles[index] }}
                </label>
            </section>
            <div v-for="(command, index) in commands">
                <section v-if="shows[index]">
                    <input type="text" v-model="titles[index]"> 
                    <span>({{ commands[index].length }})</span>
                    <textarea v-model="commands[index]" cols="80" rows="10"></textarea>
                    <button @click="sendCommand(index)">RUN ({{ counter }})</button>
                    <pre>{{ results[index] }}</pre>
                </section>
            </div>
            <section>
                <button @click="upgradePlugin">upgrade Express plugin</button>
                <button @click="showPopup=true">popup</button>
                <div class="xpPopup" v-show="showPopup">
                    <h3>Info</h3>
                    <button @click="showPopup=false">X</button>
                    <pre>{{ popupContent }}</pre>
                </div>
            </section>
        </div>

        <script type="module">
        import * as Vue from 'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.esm-browser.prod.js';
        
        const appxconfig = {
            methods: {
                async upgradePlugin() {
                    this.counter++;
                    let fd = new FormData();
                    fd.append('command', 'git?branch=master&url=https://github.com/xoomcoder/xoom-express');
                    let response = await fetch('/@/api', {
                        method: 'POST',
                        body: fd,
                    });
                    let json = await response.json();
                    console.log(json);
                    if (json.form_result) {
                        this.popupContent = json.form_result;
                        this.showPopup = true;
                    }
                },
                addTerminal () {
                    this.results.push('');
                    this.commands.push('');
                    this.titles.push('terminal' + ( 1 + this.titles.length));
                    this.shows.push(true);
                },
                async sendCommand (index) {
                    this.counter++;
                    let fd = new FormData();
                    fd.append('command', this.commands[index]);
                    let response = await fetch('/@/api', {
                        method: 'POST',
                        body: fd,
                    });
                    let json = await response.json();
                    console.log(json);
                    if (json.form_result) {
                        this.results[index] = json.form_result;
                    }
                }
            },
            data() {
              return {
                    popupContent: '...',
                    showPopup: false,
                    titles: [ 'terminal1', 'terminal2', 'terminal3' ],
                    shows: [ true, true, true ],
                    results: [ '', '', '' ],
                    commands: [ '', '', '' ],
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
        $template = Express::build_response($template);

        return $template;

    }

    static function build_response ($template)
    {
        $now = time();
        $uri = $_SERVER["REQUEST_URI"];
        // warning: create variables
        extract(parse_url($uri));

        $json = [];

        $json["time"] = date("Y-m-d H:i:s", $now);
        $json["uri"] = $uri;
        $json["request"] = $_REQUEST;

        $out = "";
        if ($path == "/@/api") {
            ob_start();
            Express::process_form();
            $json["form_result"] = ob_get_clean();
            $out = "json";
        }

        if ($out == "json") {
            status_header(200);
            header("Content-Type: application/json");
            $response = json_encode($json);
            echo $response;
            $template = "";    
        }

        $mydir = dirname(__DIR__) . "/my";
        // log
        $log_file = "$mydir/log.txt";
        if (is_file($log_file))
            file_put_contents($log_file, "$response\n", FILE_APPEND);

        if (is_file("$mydir/maintenance.json")) {
            $tab_maintenance = json_decode(file_get_contents("$mydir/maintenance.json"), true);
            if (is_array($tab_maintenance)) {
                $template = $tab_maintenance["template"] ?? "";
            }
        }

        return $template;
    }

    static function process_form ()
    {
        $command = $_REQUEST["command"] ?? "";
        if ($command) {
            Express::run_command($command);
        }

        // base64 encoding makes easier GET forms
        $b64json = $_REQUEST["b64json"] ?? "";
        if ($b64json) {
            $json = base64_decode($b64json);
            if ($json !== false) {
                $form = json_decode($json, true);
                if (is_array($json)) {
                    $action = $json["action"] ?? "";
                    $call = "ApiPublic::$action";
                    if (is_callable($call)) {
                        // load apiKey
                        V::set("apiKey", get_option("xp_apiKey"));

                        $call($json);
                    }
                }
            }
        }
    }

    static function run_command ($command, $capability="manage_options")
    {
        // FIXME: improve blocking too many recursive calls
        static $nb_recursive = 0;
        $nb_recursive++;

        if (($nb_recursive < 100) && ($command != "")) {
            if ($capability && !current_user_can($capability)) return "...\n";

            $lines = explode("\n", $command);
            foreach($lines as $number => $line) {
                $line = trim($line);
                extract(parse_url($line));
                $path ??= "";
                $query ??= "";
                
                $call = "ApiAdmin::$path";            
                if (is_callable($call)) {
    
                    $params = [];
                    parse_str($query, $params);
            
                    $call($params);
                }
                // reset
                $query = "";
                $path = "";
            }    
        }

        $nb_recursive--;

    }
    //@end
}