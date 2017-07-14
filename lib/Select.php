<?php
/**
 * Select. A Query-object implementation. Minimalistic but powerful.
 *
 * @author Victor Bolshov <victor.bolshov@emesa.nl>
 * @phpcs
 */


namespace tinyorm;


class Select
{
    /**
     * @var DbInterface
     */
    static protected $defaultConnection;
    private $from;
    private $cols;
    private $colsBind = [];
    private $joins = [];
    private $where = [];
    private $groupBy = [];
    private $having = [];
    private $orderBy = [];
    private $limit = 0;
    private $offset = 0;
    private $id;
    private $options = [];
    /**
     * @var DbInterface
     */
    private $db;
    private $fetchMode;

    /**
     * Run arbitrary SQL statement.
     * @param string $sql
     * @param array $bind
     * @param Db|null $db
     * @return \PDOStatement
     */
    static function query(string $sql, array $bind = [], Db $db = null)
    {
        $db = $db ?? self::$defaultConnection;
        $stmt = $db->prepare($sql);
        $stmt->execute($bind);
        return $stmt;
    }
    /**
     * Select constructor.
     * @param string $from
     * @param string $cols
     * @param mixed $bind1,.. $bindXXX - parameters to bind to placeholders in columns SQL expression
     */
    function __construct($from, $cols = "*", ...$bind)
    {
        $this->from = $from;
        $this->replaceColumns($cols, ...$bind);
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
        return $this;
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
     */
    static function setDefaultConnection(DbInterface $db)
    {
        self::$defaultConnection = $db;
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
     * @return DbInterface
     */
    function getConnection()
    {
        return $this->db ?: self::$defaultConnection;
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
        $stmt = $this->getConnection()->prepare($sql);
        if ($this->fetchMode) {
            $stmt->setFetchMode($this->fetchMode[0], $this->fetchMode[1], $this->fetchMode[2]);
        }
        $this->bindValues($stmt, $bind);
        $stmt->execute();
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
        $stmt = $this->getConnection()->prepare($sql);
        $this->bindValues($stmt, $bind);
        $stmt->execute();
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
     * @param array ...$bind
     * @return $this
     */
    function join($sql, $cols = null, ...$bind)
    {
        $this->joins[$sql] = $bind;
        if ($cols) {
            $this->cols .= ", {$cols}";
        }
        return $this;
    }

    /**
     * @return $this
     */
    function resetJoins()
    {
        $this->joins = [];
        return $this;
    }

    /**
     * @param $sql
     * @param array ...$bind
     * @return $this
     */
    function where($sql, ...$bind)
    {
        $this->where[$sql] = $bind;
        return $this;
    }

    /**
     * @return $this
     */
    function resetWhere()
    {
        $this->where = [];
        return $this;
    }

    /**
     * @param $sql
     * @param array ...$bind
     * @return $this
     */
    function groupBy($sql, ...$bind)
    {
        $this->groupBy[$sql] = $bind;
        return $this;
    }

    /**
     * @return $this
     */
    function resetGroupBy()
    {
        $this->groupBy = [];
        return $this;
    }

    /**
     * @param $sql
     * @param array ...$bind
     * @return $this
     */
    function having($sql, ...$bind)
    {
        $this->having[$sql] = $bind;
        return $this;
    }

    /**
     * @return $this
     */
    function resetHaving()
    {
        $this->having = [];
        return $this;
    }

    /**
     * @param $sql
     * @param array ...$bind
     * @return $this
     */
    function orderBy($sql, ...$bind)
    {
        $this->orderBy[$sql] = $bind;
        return $this;
    }

    /**
     * @return $this
     */
    function resetOrderBy()
    {
        $this->orderBy = [];
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
     * Add query option (like SQL_NO_CACHE or SQL_CALC_FOUND_ROWS)
     * @param string $option
     */
    function option(string $option)
    {
        $this->options[] = $option;
        return $this;
    }

    /**
     * @return $this
     */
    function resetOptions()
    {
        $this->options = [];
        return $this;
    }

    /**
     * @param string $columns
     * @param array ...$bind
     * @return $this
     */
    function appendColumns(string $columns, ...$bind)
    {
        $this->cols = join(", ", [$this->cols, $columns]);
        $this->colsBind = array_merge($this->colsBind, $bind);
        return $this;
    }

    /**
     * @param string $columns
     * @param array ...$bind
     * @return $this
     */
    function replaceColumns(string $columns, ...$bind)
    {
        $this->cols = $columns;
        $this->colsBind = $bind;
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
        $optionsStr = join(" ", $this->options);
        if ($this->colsBind) {
            $toInflate = [$this->cols => $colsBind];
            list($colsSql, $bind) = $this->inflate($toInflate, [], " ");
            $sql = "SELECT $optionsStr $idStr {$colsSql} FROM {$this->from}";
        } else {
            $sql = "SELECT $optionsStr $idStr {$cols} FROM {$this->from}";
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

    private function bindValues(\PDOStatement $stmt, $bind)
    {
        foreach ($bind as $i => $boundValue) {
            if ($boundValue instanceof Bind) {
                $stmt->bindValue($i + 1, $boundValue->getValue(), $boundValue->getType());
            } else {
                $stmt->bindValue($i + 1, $boundValue);
            }
        }
    }
}