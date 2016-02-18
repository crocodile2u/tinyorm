<?php

$opts = getopt("", [
    "host:",
    "port:",
    "dbname:",
    "unix_socket:",
    "charset:",
    "user:",
    "password:",
    "table:",
    "class:",
    "file:",
    "defaults:",
    "auto:",
]);

include_once __DIR__ . "/../lib/autoload.inc";

$generator = new \tinyorm\scaffold\EntityGenerator();
$generator->setHost(@$opts["host"])
    ->setPort(@$opts["port"])
    ->setDbname(@$opts["dbname"])
    ->setUnixSocket(@$opts["unix_socket"])
    ->setCharset(@$opts["charset"])
    ->setUser(@$opts["user"])
    ->setPassword(@$opts["password"])
    ->setTable(@$opts["table"])
    ->setClass(@$opts["class"])
    ->setFile(@$opts["file"])
    ->setDefaults(@$opts["defaults"])
    ->setAuto(@$opts["auto"])
    ->generate();