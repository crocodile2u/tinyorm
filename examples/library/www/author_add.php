<?php

use \library\Author,
    \library\Registry;

include __DIR__ . "/../bootstrap.php";

if ("POST" == $_SERVER["REQUEST_METHOD"]) {
    $author = new Author();
    $author->name = trim($_POST["name"]);
    if ($author->name) {
        Registry::persistenceDriver()->save($author);
        header("Location: authors.php");
        exit;
    } else {
        echo "Empty name";
    }
}
