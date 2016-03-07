<?php

spl_autoload_register(function($class) {
    if (0 === strpos($class, "todolist\\")) {
        $namespaceStripped = substr($class, 9);
        $path = __DIR__ . "/" . str_replace("\\", "/", $namespaceStripped) . ".php";
        include $path;
    }
});