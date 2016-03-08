<?php

use \library\Book,
    \library\Registry;

include __DIR__ . "/../bootstrap.php";

if ("POST" == $_SERVER["REQUEST_METHOD"]) {
    $book = new Book();
    $book->title = trim($_POST["title"]);
    if ($book->title) {
        Registry::persistenceDriver()->save($book);
        header("Location: books.php");
        exit;
    } else {
        echo "Empty title";
    }
}
