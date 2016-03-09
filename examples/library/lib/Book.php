<?php

namespace library;

use tinyorm\Select;

class Book extends \library\scaffold\Book {
    function getAuthors()
    {
        return (new Select("author", "author.*"))
            ->setFetchClass(Author::class)
            ->join("JOIN book_has_author AS bha ON (bha.author_id = author.id)")
            ->where("bha.book_id = ?", $this->id)
            ->execute()
            ->fetchAll();
    }
}