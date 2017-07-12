<?php

include_once __DIR__ . "/../lib/autoload.inc";

/**
 * @return \tinyorm\Db
 */
function get_test_connection() {
    return new \tinyorm\Db(
        "mysql:host=localhost;dbname=test;",
        "root",
        "",
        [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ]
    );
}

$connection = get_test_connection();
$connection->exec("DROP TABLE IF EXISTS test");
$connection->exec("DROP TABLE IF EXISTS test2");
$connection->exec("CREATE TABLE test (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, c_varchar varchar(10), c_int int, c_unique varchar(10) unique, INDEX c_int (c_int)) ENGINE INNODB");
$connection->exec("CREATE TABLE test2 (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, c_varchar varchar(10), c_int int, c_unique varchar(10) unique, INDEX c_int (c_int)) ENGINE INNODB");

unset($connection);