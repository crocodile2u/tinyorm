<?php

use \tinyorm\Select;

include __DIR__ . "/../bootstrap.php";

$books = (new Select("book"))->orderBy("title")->execute();

echo \library\View::render("header.php", [
    "title" => "Tinyorm Library: Books",
    "description" => \library\View::render("sidebar/books.html"),
]);

echo \library\View::render(
    "books.php",
    [
        "books" => $books,
    ]
);

echo \library\View::render("footer.php"); ?>