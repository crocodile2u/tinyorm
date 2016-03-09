<?php

namespace library;

use library\scaffold\BookHasAuthor;
use tinyorm\Select;

class Book extends \library\scaffold\Book {
    /**
     * @return Select
     */
    function getAuthors()
    {
        return (new Select("author", "author.*"))
            ->join("JOIN book_has_author AS bha ON (bha.author_id = author.id)")
            ->where("bha.book_id = ?", $this->id);
    }

    /**
     * @param int $authorId
     */
    function addAuthor($authorId)
    {
        $link = new BookHasAuthor();
        $link->book_id = $this->id;
        $link->author_id = (int) $authorId;
        Registry::persistenceDriver()->save($link);
    }

    /**
     * @param int $authorId
     * @return bool
     */
    function hasAuthor($authorId)
    {
        return (bool) (new Select("book_has_author", "1"))
            ->where("book_id = ?", $this->id)
            ->where("author_id = ?", (int) $authorId)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return Select
     */
    function getEditions()
    {
        return (new Select("edition"))
            ->where("edition.book_id = ?", $this->id);
    }
}