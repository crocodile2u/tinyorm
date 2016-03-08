<?php

spl_autoload_register(function($class) {
    if (0 === strpos($class, "library\\")) {
        $namespaceStripped = substr($class, 8);
        $path = __DIR__ . "/" . str_replace("\\", "/", $namespaceStripped) . ".php";
        include $path;
    }
});