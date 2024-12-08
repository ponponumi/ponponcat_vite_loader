<?php

require_once __DIR__ . "/vendor/autoload.php";

(function () {
    $viteLoader = new \Ponponumi\PonponcatViteLoader\Load(__DIR__ . "/build/.vite/manifest.json",get_template_directory_uri() . "/build");

    $viteLoader->filesSet([
        "assets/scss/style.scss",
        "assets/ts/script.ts",
    ]);

    $viteLoader->filesSet([
        "assets/ts/head.ts",
    ],"head");

    $viteLoader->filesSet([
        "assets/ts/footer.ts",
    ],"footer");

    $viteLoader->load();

    var_dump($viteLoader);
})();
