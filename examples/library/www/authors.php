<?php

use \tinyorm\Select;

include __DIR__ . "/../bootstrap.php";

$authors = (new Select("author"))->orderBy("name")->execute();

echo \library\View::render("header.php", [
    "title" => "Tinyorm Library: Authors",
    "description" => \library\View::render("sidebar/authors.html"),
]);

echo \library\View::render(
    "authors.php",
    [
        "authors" => $authors,
    ]
);

echo \library\View::render("footer.php"); ?>