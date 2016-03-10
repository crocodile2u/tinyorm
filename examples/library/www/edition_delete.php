<?php

use \library\Registry,
    \library\scaffold\Edition;

include __DIR__ . "/../bootstrap.php";

if (empty($_GET["id"])) {
    die("No edition ID provided");
}

$edition = Registry::persistenceDriver()->find((int) $_GET["id"], new Edition());

if (!$edition) {
    die("Edition #" . (int) $_GET["id"] . " not found");
}

Registry::persistenceDriver()->delete($edition);

header("Location: book_edit.php?id=" . $edition->book_id);