<?php
include __DIR__ . "/../bootstrap.php";

echo \library\View::render("header.php", [
    "title" => "Tinyorm Library Example Home",
    "description" => \library\View::render("sidebar/index.html"),
]);

echo \library\View::render(
    "index.php"
);

echo \library\View::render("footer.php"); ?>