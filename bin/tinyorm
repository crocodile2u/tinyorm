#!/usr/bin/env php
<?php
error_reporting(E_ALL);
ini_set("display_errors", "On");
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
    "config:",
    "noconfig",
    "yes",
]);

$noConfig = isset($opts["noconfig"]);
if ($noConfig) {
    $settings = [];
} else {
    $configFileRelPath = $opts["config"] ?? ".tinyorm";
    if ("/" === $configFileRelPath[0]) {
        $configFileAbsPath = $configFileRelPath;
    } else {
        $configFileAbsPath = getcwd() . "/" . $configFileRelPath;
    }

    if (is_readable($configFileAbsPath)) {
        $settings = @json_decode(file_get_contents($configFileAbsPath), true) ?: [];
    } elseif (file_exists($configFileAbsPath)) {
        fwrite(STDERR, "Configuration file $configFileRelPath is not readable");
        exit(1);
    } else {
        $settings = [];
    }
}

$opts = ($opts ?: []) + $settings;

$updatedSettings = array_intersect_key(
    $opts,
    [
        "host" => 1,
        "port" => 1,
        "dbname" => 1,
        "unix_socket" => 1,
        "charset" => 1,
        "user" => 1,
        "password" => 1,
    ]
);

$autoloads = [__DIR__ . "/../autoload.php", __DIR__ . "/../vendor/autoload.php"];
$autoloadFound = false;
foreach ($autoloads as $autoload) {
    if (is_file($autoload)) {
        include_once $autoload;
        $autoloadFound = true;
        break;
    }
}

if (!$autoloadFound) {
    echo "autoload.php not found! Searched:\n\t" . join("\n\t", $autoloads) . "\n";
    exit(1);
}

$generator = new \tinyorm\scaffold\EntityGenerator();
$phpCode = $generator->setHost(@$opts["host"])
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
if (isset($opts["file"])) {
    if (!is_writable($opts["file"])) {
        echo "{$opts["file"]} is not writable\n";
        exit(1);
    }
    $fp = fopen($opts["file"], "w");
} else {
    $fp = STDOUT;
}
fwrite($fp, $phpCode);

if ($noConfig) {
    exit(0);
}

if ($updatedSettings != $settings) {
    $settingsJson = json_encode($updatedSettings);
    $silentUpdate = isset($opts["yes"]);
    if ($silentUpdate) {
        $overwriteConfig = true;
    } else {
        fwrite(STDERR, "\n\nUpdate settings with $settingsJson? Y/N [Y]\n");
        $answer = trim(fgets(STDIN));
        if (!trim($answer)) {
            $answer = "Y";
        }
        switch (strtolower($answer)) {
            case "y":
            case "yes":
                $overwriteConfig = true;
                break;
            default:
                $overwriteConfig = false;
                break;
        }
    }
    if ($overwriteConfig) {
        if (!file_put_contents($configFileAbsPath, $settingsJson)) {
            fwrite(STDERR, "$configFileRelPath is not writable\n");
            exit(1);
        }
    }
}
