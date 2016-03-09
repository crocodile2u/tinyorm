<?php

use \library\Registry,
    \library\scaffold\Edition;

include __DIR__ . "/../bootstrap.php";

$edition = new Edition($_POST);
$edition->id = ((int) $edition->id) ?: null;
$edition->book_id = (int) $edition->book_id;
$edition->year = (int) $edition->year;
$edition->isbn = (string) $edition->isbn;
$edition->instance_count = (int) $edition->instance_count;

Registry::persistenceDriver()->save($edition);

header("Location: book_edit.php?id={$edition->book_id}");
exit;