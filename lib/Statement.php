<?php

namespace tinyorm;

class Statement extends \PDOStatement
{
    /** @var Db */
    private $db;

    protected function __construct(Db $db)
    {
        $this->db = $db;
    }

    function execute($input_parameters = null)
    {
        $this->db->beginTransactionIfNeeded();
        return parent::execute($input_parameters);
    }
}