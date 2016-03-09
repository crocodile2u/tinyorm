<?php

use \library\Registry,
    \library\Book;

include __DIR__ . "/../bootstrap.php";

if (empty($_POST["id"])) {
    die("No book ID provided");
}

$id = (int) $_POST["id"];
$book = Registry::persistenceDriver()->find($id, new Book());
if (!$book) {
    die("Book ID #" . (int) $_POST["id"] . " not found");
}

$book->title = trim($_POST["title"]);

if (!$book->title) {
    die("No book title provided");
}

Registry::persistenceDriver()->save($book);

header("Location: book_edit.php?id={$book->id}");
exit;