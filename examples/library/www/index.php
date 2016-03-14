<?php

use \tinyorm\Select;

include __DIR__ . "/../bootstrap.php";

echo \library\View::render("header.php", [
    "title" => "Tinyorm Library Example Home",
    "description" => \library\View::render("sidebar/index.html"),
]);

$bookCount = (new Select("book"))->count();
$authorCount = (new Select("author"))->count();
$editionCount = (new Select("edition"))->count();
$stats = (new Select(
    "edition",
    "AVG(instance_count) AS avg_instance_count,
        SUM(instance_count) AS total_instance_count"))
    ->execute()
    ->fetch();
$instanceCount = $stats["total_instance_count"];
$instanceAvg = $stats["avg_instance_count"];

echo \library\View::render(
    "index.php",
    [
        "bookCount" => $bookCount,
        "authorCount" => $authorCount,
        "instanceCount" => $instanceCount,
        "editionCount" => $editionCount,
        "instanceAvg" => $instanceAvg,
    ]
);

echo \library\View::render("footer.php"); ?>