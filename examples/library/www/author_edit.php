<?php

use \library\Registry,
    \library\Author;

include __DIR__ . "/../bootstrap.php";

if (empty($_GET["id"])) {
    die("No author ID provided");
} else {
    $author = Registry::persistenceDriver()->find((int) $_GET["id"], new Author());
    if (!$author) {
        die("Author ID #" . (int) $_GET["id"] . " not found");
    }
}

echo \library\View::render("header.php", [
    "title" => "Tinyorm Library: Edit author",
    "description" => \library\View::render("sidebar/author_edit.html"),
]);

echo \library\View::render(
    "author_edit.php",
    [
        "author" => $author,
    ]
);

echo \library\View::render("footer.php"); ?>