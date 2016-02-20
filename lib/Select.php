<?php
/**
 * Copyright (c) 2004-2015, EMESA BV
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are PROHIBITED without prior written permission from
 * the author. This product may NOT be used anywhere and on any computer
 * except the server platform of EMESA BV. If you received this code
 * accidentally and without intent to use it, please report this
 * incident to the author by email.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT
 * OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */
/**
 *
 *
 * @author Victor Bolshov <victor.bolshov@emesa.nl>
 * @phpcs
 */


namespace tinyorm;


class Select
{
    private $from;
    private $cols;
    private $colsBind;
    private $joins = [];
    private $where = [];
    private $groupBy = [];
    private $having = [];
    private $orderBy = [];
    private $limit = 0;
    private $offset = 0;
    private $id;
    /**
     * @var DbInterface
     */
    private $db;
    private $fetchMode;
    /**
     * Select constructor.
     * @param string $from
     * @param string $cols
     */
    function __construct($from, $cols = "*")
    {
        $this->from = $from;
        $this->cols = $cols;
        $this->colsBind = array_slice(func_get_args(), 2);
    }

    /**
     * The resulting SQL query will contain this id in a comment.
     * This way it can be easily found in logs.
     * @param $id
     */
    function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @see http://php.net/manual/en/pdostatement.setfetchmode.php
     * @param int $mode
     * @param mixed $arg1
     * @param mixed $arg2
     * @return $this
     */
    function setFetchMode($mode, $arg1 = null, $arg2 = null)
    {
        $this->fetchMode = [$mode, $arg1, $arg2];
    }

    /**
     * @param Entity|object $prototype
     * @return $this
     */
    function setFetchInto($prototype)
    {
        return $this->setFetchMode(\PDO::FETCH_INTO, $prototype);
    }

    /**
     * @param Entity|object|string $class
     * @return $this
     */
    function setFetchClass($class, $ctorArgs = [])
    {
        $class = is_object($class) ? get_class($class) : (string) $class;
        return $this->setFetchMode(\PDO::FETCH_CLASS, $class, $ctorArgs);
    }

    /**
     * @param DbInterface $db
     * @return $this
     */
    function setConnection(DbInterface $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @return \PDOStatement
     */
    function execute()
    {
        list($sql, $bind) = $this->compose(
            $this->cols,
            $this->groupBy,
            $this->having,
            $this->limit,
            $this->colsBind,
            $this->id
        );
        $stmt = $this->db->prepare($sql);
        if ($this->fetchMode) {
            $stmt->setFetchMode($this->fetchMode[0], $this->fetchMode[1], $this->fetchMode[2]);
        }
        $stmt->execute($bind);
        return $stmt;
    }

    function count($expr = "*")
    {
        return $this->getCountStatement($expr)->fetchColumn();
    }

    /**
     * @param string $expr
     * @return \PDOStatement
     */
    function getCountStatement($expr = "*")
    {
        list($sql, $bind) = $this->compose(
            "COUNT($expr)",
            false,
            false,
            0,
            null,
            $this->id ? ($this->id . ".count") : null
        );
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind);
        return $stmt;
    }

    /**
     * Get a string representation of a query.
     *
     * @return string
     */
    function __toString()
    {
        list($sql, $bind) = $this->compose(
            $this->cols,
            $this->groupBy,
            $this->having,
            $this->limit,
            $this->colsBind,
            $this->id
        );
        $parts = explode("?", $sql);
        $ret = "";
        foreach ($parts as $i => $sqlPart) {
            $ret .= $sqlPart;
            if (isset($bind[$i])) {
                $ret .= "'" . addslashes($bind[$i]) . "'";
            }
        }

        return $ret;
    }

    /**
     * @param string $sql
     * @param string $cols
     * @return $this
     */
    function join($sql, $cols = null)
    {
        $this->joins[$sql] = array_slice(func_get_args(), 2);
        if ($cols) {
            $this->cols .= ", {$cols}";
        }
        return $this;
    }

    /**
     * @param $sql
     * @return $this
     */
    function where($sql)
    {
        $this->where[$sql] = array_slice(func_get_args(), 1);
        return $this;
    }

    /**
     * @param $sql
     * @return $this
     */
    function groupBy($sql)
    {
        $this->groupBy[$sql] = array_slice(func_get_args(), 1);
        return $this;
    }

    /**
     * @param $sql
     * @return $this
     */
    function having($sql)
    {
        $this->having[$sql] = array_slice(func_get_args(), 1);
        return $this;
    }

    /**
     * @param $sql
     * @return $this
     */
    function orderBy($sql)
    {
        $this->orderBy[$sql] = array_slice(func_get_args(), 1);
        return $this;
    }

    /**
     * @param int $n
     * @return $this
     */
    function limit($n)
    {
        $this->limit = $n;
        return $this;
    }

    /**
     * @param int $n
     * @return $this
     */
    function offset($n)
    {
        $this->offset = $n;
        return $this;
    }

    /**
     * @param int $pageSize
     * @param int $pageNumber
     * @return $this
     */
    function paginate($pageSize, $pageNumber)
    {
        return $this->limit($pageSize)->offset(($pageNumber - 1) * $pageSize);
    }

    private function compose($cols, $groupBy, $having, $limit, $colsBind, $id)
    {
        $idStr = $id ? "/* $id */" : "";
        if ($this->colsBind) {
            $toInflate = [$this->cols => $colsBind];
            list($colsSql, $bind) = $this->inflate($toInflate, [], " ");
            $sql = "SELECT $idStr {$colsSql} FROM {$this->from}";
        } else {
            $sql = "SELECT $idStr {$cols} FROM {$this->from}";
            $bind = [];
        }

        if (count($this->joins)) {
            list($joinSql, $bind) = $this->inflate($this->joins, $bind, " ");
            $sql .= " " . $joinSql;
        }

        if (count($this->where)) {
            list($whereSql, $bind) = $this->inflate($this->where, $bind, ") AND (");
            $sql .= " WHERE (" . $whereSql . ")";
        }

        if ($groupBy) {
            list($groupBySql, $bind) = $this->inflate($groupBy, $bind, ", ");
            $sql .= " GROUP BY " . $groupBySql;
        }

        if ($having) {
            list($havingSql, $bind) = $this->inflate($having, $bind, ", ");
            $sql .= " HAVING " . $havingSql;
        }

        if (count($this->orderBy)) {
            list($orderBySql, $bind) = $this->inflate($this->orderBy, $bind, ", ");
            $sql .= " ORDER BY " . $orderBySql . "";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$this->offset}";
        }

        return [$sql, $bind];
    }

    private function inflate($sqlBind, array $inflatedBind, $delimiter)
    {
        $inflatedSql = [];
        foreach ($sqlBind as $sql => $bind) {
            list($inflatedSqlPart, $inflatedBindPart) = $this->inflatePart($sql, $bind);
            $inflatedSql[] = $inflatedSqlPart;
            $inflatedBind = array_merge($inflatedBind, $inflatedBindPart);
        }
        return [join($delimiter, $inflatedSql), $inflatedBind];
    }

    private function inflatePart($sql, $bind)
    {
        $exp = explode("?", $sql);
        $inflatedSql = "";
        $inflatedBind = [];
        foreach ($exp as $i => $str) {
            $inflatedSql .= $str;
            if (isset($bind[$i])) {
                if (is_array($bind[$i])) {
                    // WHERE col IN (?): inflate.
                    $inflatedSql .= str_repeat("?, ", count($bind[$i]));
                    $inflatedSql = rtrim($inflatedSql, ", ");
                    $inflatedBind = array_merge($inflatedBind, $bind[$i]);
                } else {
                    // Regular placeholder.
                    $inflatedSql .= "?";
                    $inflatedBind[] = $bind[$i];
                }
            }
        }

        return [$inflatedSql, $inflatedBind];
    }
}