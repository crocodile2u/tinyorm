<?php

use \library\Author,
    \library\Registry;

include __DIR__ . "/../bootstrap.php";

$author = new Author($_POST);
$author->id = ((int) $author->id) ?: null;
$author->name = trim((string) $author->name);

if ($author->name) {
    Registry::persistenceDriver()->save($author);
    header("Location: authors.php");
    exit;
} else {
    echo "Empty name";
}
