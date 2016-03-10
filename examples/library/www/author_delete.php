<?php

use \library\Registry,
    \library\Author;

include __DIR__ . "/../bootstrap.php";

if (empty($_GET["id"])) {
    die("No author ID provided");
}

/** @var Author $author */
$author = Registry::persistenceDriver()->find((int) $_GET["id"], new Author());

if (!$author) {
    die("Author #" . (int) $_GET["id"] . " not found");
}

Registry::persistenceDriver()->delete($author);

header("Location: authors.php");