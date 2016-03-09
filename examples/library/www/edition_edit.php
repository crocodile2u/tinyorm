<?php

use \library\Registry,
    \library\scaffold\Edition;

include __DIR__ . "/../bootstrap.php";

if (empty($_GET["id"])) {
    if (empty($_GET["book_id"])) {
        die("No book ID provided");
    }
    $edition = new Edition();
    $edition->book_id = (int) $_GET["book_id"];
} else {
    $edition = Registry::persistenceDriver()->find((int) $_GET["id"], new Edition());
    if (!$edition) {
        die("Edition ID #" . (int) $_GET["id"] . " not found");
    }
}

$title = $edition->id ? "Edit book edition" : "Add book edition";

echo \library\View::render("header.php", [
    "title" => "Tinyorm Library: $title",
    "description" => \library\View::render("sidebar/edition_edit.html"),
]);

echo \library\View::render(
    "edition_edit.php",
    [
        "edition" => $edition,
    ]
);

echo \library\View::render("footer.php"); ?>