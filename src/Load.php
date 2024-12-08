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

    public function __construct(string $manifestPath,string $buildPath,$errorMode=false)
    {
        $this->viteLoader = new \Ponponumi\ViteLoader\ViteLoader($manifestPath, $buildPath, $errorMode);
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

    private function idCreate(string $path)
    {
        // IDを作る
        return $this->idStart . "_" . pathinfo($path, PATHINFO_FILENAME);
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
}
