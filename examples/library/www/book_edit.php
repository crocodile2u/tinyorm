<?php

use \library\Registry,
    \library\Book;

include __DIR__ . "/../bootstrap.php";

if (empty($_GET["id"])) {
    die("No book ID provided");
}

$book = Registry::persistenceDriver()->find((int) $_GET["id"], new Book());
if (!$book) {
    die("Book ID #" . (int) $_GET["id"] . " not found");
}

echo \library\View::render("header.php", [
    "title" => "Tinyorm Library: Edit book",
    "description" => \library\View::render("sidebar/book_edit.html"),
]);

echo \library\View::render(
    "book_edit.php",
    [
        "book" => $book,
    ]
);

echo \library\View::render("footer.php"); ?>