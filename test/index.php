<?php

require_once __DIR__ . "/vendor/autoload.php";

(function () {
    $viteLoader = new \Ponponumi\PonponcatViteLoader\Load(__DIR__ . "/build/.vite/manifest.json","/build");
})();
