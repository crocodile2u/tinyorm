<?php

use \library\Registry,
    \tinyorm\Select;

error_reporting(E_ALL);
ini_set("display_errors", "On");
ini_set("log_errors", "Off");

define("TODOLIST_ROOT", __DIR__ . "/");

// tinyorm autoload
include_once TODOLIST_ROOT . "../../vendor/autoload.php";

// application autoload
include_once TODOLIST_ROOT . "lib/autoload.inc";

// configuration
$config = include TODOLIST_ROOT . "etc/config.base.php";

$override = TODOLIST_ROOT . "etc/config.override.php";
if (is_file($override)) {
    $config = array_merge($config, include $override);
}

Registry::loadConfig($config);
Select::setDefaultConnection(Registry::db());