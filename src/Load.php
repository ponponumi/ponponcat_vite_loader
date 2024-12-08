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

    public function __construct(string $manifestPath,string $buildPath,$errorMode=false)
    {
        $this->viteLoader = new \Ponponumi\ViteLoader\ViteLoader($manifestPath, $buildPath, $errorMode);
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
}
