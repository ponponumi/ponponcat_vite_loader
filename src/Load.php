<?php

namespace Ponponumi\PonponcatViteLoader;

class Load
{
    public array $cssFiles = [];
    public array $jsModuleFiles = [];
    public array $jsFiles = [];
    public array $jsFooterFiles = [];
    public object $viteLoader;
    public $devMode = false;
    public string $idStart = "ponponcat";
    public $destructMode = false;
    private bool $loaded = false;
    private bool $idSourcePathMode = false;

    public function __construct(string $manifestPath,string $buildPath,$errorMode=false)
    {
        $this->wordpressCheck();
        $this->viteLoader = new \Ponponumi\ViteLoader\ViteLoader($manifestPath, $buildPath, $errorMode);
    }

    public function idSourcePathModeChange($value){
        $this->idSourcePathMode = boolval($value);
    }

    public function destructModeSet($value)
    {
        $this->destructMode = $value;
    }

    public function wordpressCheck()
    {
        // WordPressの関数があるかどうかどうか確認
        $addAction = function_exists("add_action");
        $wpEnqueueStyle = function_exists("wp_enqueue_style");
        $wpEnqueueScript = function_exists("wp_enqueue_script");
        $wpEnqueueScriptModule = function_exists("wp_enqueue_script_module");
        $getLocale = function_exists("get_locale");

        if(!$addAction || !$wpEnqueueStyle || !$wpEnqueueScript || !$wpEnqueueScriptModule || !$getLocale){
            $message = "WordPress以外の環境では、本ライブラリを使用できません。";
            $message .= "\nThis library cannot be used in environments other than WordPress.";

            throw new \Exception($message);
        }
    }

    public function idStartSet(string $idStart="")
    {
        if($idStart !== ""){
            $this->idStart = $idStart;
        }else{
            $this->idStart = "ponponcat";
        }
    }

    public function devSet($devMode=false,string $devHost="",string $devHostWeb=""){
        // デバッグモードを設定する
        if($devMode){
            $this->viteLoader->devServerSetting($devMode, $devHost, $devHostWeb);

            // リロード用スクリプトを取得し、失敗すればデバッグモードは無効にする
            $reloadHTML = $this->viteLoader->viteReloadHtmlGet(false);
            $this->devMode = $reloadHTML ? true : false;
        }else{
            $this->devMode = false;
        }
    }

    private function fileSet(array $file, string $scriptMode, string $reloadPath)
    {
        // ファイルをプロパティにセットする
        if($file["type"] === "style"){
            // CSSなら、無条件でCSSファイルとしてセットする
            $this->cssFiles[] = $file["path"];
        }elseif($file["type"] === "script"){
            // JavaScriptなら
            if($file["path"] === $reloadPath){
                // リロード用スクリプトなら、モジュールに組み込む
                $this->jsModuleFiles[] = $file["path"];
            }else{
                // リロード用以外なら、状況によって分ける
                switch($scriptMode){
                    case "head":
                        // 通常のスクリプトで、headに読み込ませるなら
                        $this->jsFiles[] = $file["path"];
                        break;
                    case "footer":
                        // 通常のスクリプトで、フッターに読み込ませるなら
                        $this->jsFooterFiles[] = $file["path"];
                        break;
                    default:
                        // モジュールにするなら
                        $this->jsModuleFiles[] = $file["path"];
                        break;
                }
            }
        }
    }

    public function filesSet(array $files,string $scriptMode="module")
    {
        // ファイルをセットする
        $reloadPath = $this->viteLoader->viteReloadPathGet(false);
        $webFiles = $this->viteLoader->typeWebPathListGet($files);

        foreach($webFiles as $webFile){
            $this->fileSet($webFile, $scriptMode, $reloadPath);
        }
    }

    public function filesSetLoad(array $files,string $scriptMode="module")
    {
        $this->filesSet($files, $scriptMode);
        $this->load();
    }

    private function idCreate(string $path)
    {
        // IDを作る
        if($this->idSourcePathMode){
            // ソースパスベースなら
            $sourcePath = $this->viteLoader->sourcePathGet($path);
            return $this->idStart . "_" . preg_replace("/[^a-zA-Z0-9_-]/","-",$sourcePath);
        }else{
            // ファイル名ベースなら
            return $this->idStart . "_" . pathinfo($path, PATHINFO_FILENAME);
        }
    }

    private function loadRoopCallback(array $files,callable $func)
    {
        if($files !== []){
            foreach($files as $file){
                $func($file);
            }
        }
    }

    private function jsLoadDev(array $files)
    {
        $this->loadRoopCallback($files, function ($file) {
            echo $this->viteLoader->jsLinkCreate($file);
        });
    }

    private function moduleLoadDev(array $files)
    {

        $this->loadRoopCallback($files, function ($file) {
            echo $this->viteLoader->moduleLinkCreate($file);
        });
    }

    private function cssLoadDev(array $files)
    {

        $this->loadRoopCallback($files, function ($file) {
            echo $this->viteLoader->cssLinkCreate($file);
        });
    }

    private function jsLoad(array $files)
    {
        $this->loadRoopCallback($files, function ($file) {
            wp_enqueue_script($this->idCreate($file), $file);
        });
    }

    private function jsFooterLoad(array $files)
    {
        $this->loadRoopCallback($files, function ($file) {
            wp_enqueue_script($this->idCreate($file), $file,[],false,true);
        });
    }

    private function moduleLoad(array $files)
    {
        $this->loadRoopCallback($files, function ($file) {
            wp_enqueue_script_module($this->idCreate($file), $file);
        });
    }

    private function cssLoad(array $files)
    {

        $this->loadRoopCallback($files, function ($file) {
            wp_enqueue_style($this->idCreate($file), $file);
        });
    }

    private function loadCheck(array $files1,array $files2,array $files3=[],array $files4=[]): bool
    {
        if($files1 !== [] || $files2 !== [] || $files3 !== [] || $files4 !== []){
            // どれか1つでもデータがあればtrueにする
            return true;
        }

        return false;
    }

    public function load()
    {
        if($this->loaded){
            // 既に読み込み済みであれば
            throw new \Exception("既に読み込み済みです");
        }

        if($this->devMode){
            // 開発モードであれば
            if($this->loadCheck($this->cssFiles,$this->jsFiles)){
                // headにCSSとJSを読み込み
                add_action("wp_head", function () {
                    $this->cssLoadDev($this->cssFiles);
                    $this->jsLoadDev($this->jsFiles);
                });
            }

            if($this->loadCheck($this->jsFooterFiles,$this->jsModuleFiles)){
                // bodyの最後にJSを読み込み
                add_action("wp_footer", function () {
                    $this->jsLoadDev($this->jsFooterFiles);
                    $this->moduleLoadDev($this->jsModuleFiles);
                });
            }
        }else{
            // 製品モードであれば
            if($this->loadCheck($this->cssFiles,$this->jsFiles,$this->jsFooterFiles,$this->jsModuleFiles)){
                // 全てのファイルを読み込み
                add_action("wp_enqueue_scripts", function () {
                    $this->cssLoad($this->cssFiles);
                    $this->jsLoad($this->jsFiles);
                    $this->jsFooterLoad($this->jsFooterFiles);
                    $this->moduleLoad($this->jsModuleFiles);
                });
            }
        }

        $this->loaded = true;
    }

    public function __destruct()
    {
        if($this->destructMode){
            $this->load();
        }
    }
}
