<?php

use \library\Registry,
    \library\Book;

include __DIR__ . "/../bootstrap.php";

if (empty($_POST["book_id"])) {
    die("No book ID provided");
}

if (empty($_POST["author_id"])) {
    die("No author ID provided");
}

/** @var Book $book */
$book = Registry::persistenceDriver()->find((int) $_POST["book_id"], new Book());
if (!$book) {
    die("Book ID #" . (int) $_POST["book_id"] . " not found");
}

if ($book->hasAuthor($_POST["author_id"])) {
    die("This book already has this author");
}

$book->addAuthor($_POST["author_id"]);

header("Location: book_edit.php?id=" . (int) $_POST["book_id"]);