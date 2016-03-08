<?php

use \library\Registry,
    \tinyorm\Select;

define("TODOLIST_ROOT", __DIR__ . "/");

// tinyorm autoload
include_once TODOLIST_ROOT . "../../lib/autoload.inc";

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