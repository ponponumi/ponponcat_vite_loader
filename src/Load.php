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
}
