<?php

require_once __DIR__ . "/vendor/autoload.php";

(function () {
    $viteLoader = new \Ponponumi\PonponcatViteLoader\Load(__DIR__ . "/build/.vite/manifest.json",get_template_directory_uri() . "/build");

    // if(WP_DEBUG){
    //     $viteLoader->devSet(true, $_ENV["PONPONCAT_VITE_HOST"], $_ENV["PONPONCAT_VITE_HOST_WEB"]);
    // }

    $viteLoader->idSourcePathModeChange(true);

    $viteLoader->filesSet([
        "assets/scss/style.scss",
        "assets/ts/script.ts",
    ]);

    $viteLoader->filesSet([
        "assets/ts/head.ts",
    ],"head");

    $viteLoader->filesSetLoad([
        "assets/ts/footer.ts",
        "assets/ts/fooe.ts",
    ],"footer");
})();
