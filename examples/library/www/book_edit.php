<?php

use \library\Registry,
    \library\Book,
    \tinyorm\Select;

include __DIR__ . "/../bootstrap.php";

if (empty($_GET["id"])) {
    die("No book ID provided");
}

/** @var Book $book */
$book = Registry::persistenceDriver()->find((int) $_GET["id"], new Book());
if (!$book) {
    die("Book ID #" . (int) $_GET["id"] . " not found");
}

echo \library\View::render("header.php", [
    "title" => "Tinyorm Library: Edit book",
    "description" => \library\View::render("sidebar/book_edit.html"),
]);

$allAuthors = (new Select("author"))
    ->orderBy("name")
    ->execute()
    ->fetchAll(\PDO::FETCH_KEY_PAIR);

$bookAuthors = $book->getAuthors()->execute()->fetchAll();
$bookEditions = $book->getEditions()->execute()->fetchAll();

echo \library\View::render(
    "book_edit.php",
    [
        "book" => $book,
        "allAuthors" => $allAuthors,
        "bookAuthors" => $bookAuthors,
        "bookEditions" => $bookEditions,
    ]
);

echo \library\View::render("footer.php"); ?>