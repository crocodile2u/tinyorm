<?php

namespace library;

use tinyorm\Select;

class Author extends \library\scaffold\Author {
    function getBooks()
    {
        return (new Select("book", "book.*"))
            ->setFetchClass(Book::class)
            ->join("JOIN book_has_author AS bha ON (bha.book_id = book.id)")
            ->where("bha.author_id = ?", $this->id)
            ->execute()
            ->fetchAll();
    }
}