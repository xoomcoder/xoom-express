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
    

    static function listZipFiles ()
    {
        $result = [];
        $target ??= dirname(dirname(__DIR__));
        $zipdir = "$target/xp-data";
        $result = glob("$zipdir/data-*.zip");
        if (is_array($result)) {
            $result = array_map("basename", $result);
        }

        return $result;
    }

    static function build_admin() {

        $datazip = json_encode([
            "files" => Express::listZipFiles(),
        ], JSON_PRETTY_PRINT);
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
        .tree .directory {
            background-color: #aaaaaa;
        }
        </style>
        <h1>Express</h1>
        <div id="appx">
            <section>
                <h3>Data Zip</h3>
                <nav>
                    <button v-for="f in datazip.files">{{ f }}</button>
                </nav>
                <button @click="openLocalFolder">Open Local Folder</button>
                <button @click="refreshLocalFiles">refresh list</button>
                <input type="range" min="0" max="10000" step="1000" v-model="refreshInterval">{{ refreshInterval }}s
                <input type="text" v-model="zipkey">({{ zipkey }})({{ lastSync }})
                <ul class="tree">
                    <li v-for="f in localFiles" :class="f.kind">{{ f.path + '/' + f.name }} ({{ f.modifDate }})</li>
                </ul>
            </section>
            <section>
                <h3>Terminal</h3>
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
        

        let datazip = $datazip;
        const appxconfig = {
            methods: {
                async listSubFolder (folderH, path) {
                    if (folderH == null) return [];
                    let entries = [];
                    for await(const entry of folderH.values()) {
                        // store relative path
                        entry.path = path;
                        if (entry.kind === 'file') {
                            let file = await entry.getFile();
                            entry.lastModif = file.lastModified;
                            entry.modifDate = new Date(entry.lastModif);
                        }
                        else {
                            entry.lastModif = '';
                        }
                        // console.log(entry);
                        entries.push(entry);
                        if (entry.kind === 'directory') {
                            let subentries = await this.listSubFolder(entry, path + '/' + folderH.name);
                            entries = entries.concat(subentries);
                            
                        }
                    }
                    return entries;
                },
                async refreshLocalFiles () {
                    if (this.dirH == null) return;
                    let entries = await this.listSubFolder(this.dirH, this.dirH.name);
                    this.localFiles = entries;

                    // sync files with server
                    console.log(this.zipkey);
                    if (this.zipkey != '') {
                        this.localFiles.forEach(async (f) => {
                            if (f.kind == 'directory') return;
    
                            let lastm = f.lastModif;
                            if (this.lastSync < lastm) {
                                console.log(f);
                                let fd = new FormData();
    
                                let mydata = {
                                    'action' : 'upload',
                                    'key': this.zipkey,
                                    'filename': 'upload',
                                    'zipath': f.path + '/' + f.name,
                                };
    
                                let b64json = btoa(JSON.stringify(mydata));
                                fd.append('b64json', b64json);
                    
                                // add upload file
                                let blob = await f.getFile();
                                fd.append('upload', blob, f.path + '/' + f.name);
                    
                                let response =  await fetch('https://xpress.applh.com/@/api', {
                                    method: 'POST',
                                    body: fd
                                });
                                let responseJson = await response.json();
                                console.log(responseJson);
                    
                            }
    
                        });
    
                        // update lastSync
                        this.lastSync = Date.now();
                    }

                    if (this.refreshInterval > 0)
                        setTimeout(this.refreshLocalFiles, this.refreshInterval);
                },
                async openLocalFolder () {
                    this.dirH = await window.showDirectoryPicker();
                    this.refreshLocalFiles();
                },
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
                    zipkey: '',
                    lastSync: Date.now(),
                    refreshInterval: 1000,
                    localFiles: [],
                    dirH: null,
                    datazip,
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
        // direct access to zip files
        if ("/@/file/" == substr($path, 0, 8)) {
            $datadir = WP_PLUGIN_DIR . "/xp-data";
            $zpath = str_replace("/@/file/", "", $path);
            extract(pathinfo($zpath));
            $md5 = basename($dirname);
            $filename = strtolower(preg_replace("/[^a-zA-Z0-9]/", "-", $filename));
            $extension = strtolower(preg_replace("/[^a-zA-Z0-9]/", "-", $extension));

            $searchfile = "zip://$datadir/data-$md5.zip#$filename.$extension";
            $result = @file_get_contents($searchfile);
            if (!is_null($result)) {
                $mimes = [
                    "json"  => "application/json",
                    "jpg"   => "image/jpeg",
                    "jpeg"  => "image/jpeg",
                    "png"   => "image/png",
                    "gif"   => "image/gif",
                    "svg"   => "image/svg+xml",
                    "txt"   => "text/plain",
                    "html"  => "text/html",
                    "css"   => "text/css",
                    "js"    => "application/javascript",
                    "pdf"   => "application/pdf",
                    "zip"   => "application/zip",
                    "mp3"   => "audio/mp3",
                    "mp4"   => "video/mp4",
                ];
                $mime = $mimes[$extension] ?? "application/octet-stream";
                if ($mime != "") {
                    header("Content-Type: $mime");
                }
                status_header(200);
                echo $result;
            }
        }

        if ($out == "json") {
            status_header(200);
            header("Content-Type: application/json");
            // allow access from any browser JS fetch
            header("Access-Control-Allow-Origin: *");
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
                if (is_array($form)) {
                    $action = $form["action"] ?? "";
                    $call = "ApiPublic::$action";
                    if (is_callable($call)) {
                        // load apiKey
                        V::set("apiKey", get_option("xp_apiKey"));
                        V::set("datadir", WP_PLUGIN_DIR . "/xp-data");

                        $call($form);
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