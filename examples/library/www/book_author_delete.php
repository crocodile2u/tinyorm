<?php

use \library\Registry,
    \tinyorm\Select,
    \library\scaffold\BookHasAuthor;

include __DIR__ . "/../bootstrap.php";

if (empty($_GET["book_id"])) {
    die("No book ID provided");
}

if (empty($_GET["author_id"])) {
    die("No author ID provided");
}

$bookHasAuthor = (new Select("book_has_author"))
    ->where("book_id = ?", (int) $_GET["book_id"])
    ->where("author_id = ?", (int) $_GET["author_id"])
    ->setFetchClass(BookHasAuthor::class)
    ->execute()
    ->fetch();

if (!$bookHasAuthor) {
    die("This author is not registered for this book");
}

Registry::persistenceDriver()->delete($bookHasAuthor);

header("Location: book_edit.php?id=" . (int) $_GET["book_id"]);