<?php

use \library\Registry,
    \library\Book;

include __DIR__ . "/../bootstrap.php";

if (empty($_GET["id"])) {
    die("No book ID provided");
}

/** @var Book $book */
$book = Registry::persistenceDriver()->find((int) $_GET["id"], new Book());

if (!$book) {
    die("Book #" . (int) $_GET["id"] . " not found");
}

$editionCount = $book->getEditions()->count();
if ($book->getEditions()->count()) {
    die("Cannot delete this book because it still has $editionCount edition(s) listed!");
}

Registry::persistenceDriver()->delete($book);

header("Location: books.php");