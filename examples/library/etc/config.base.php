<?php

return [
    "db.dsn" => "mysql:host=localhost;dbname=tinyorm_library;",
    "db.user" => "root",
    "db.password" => "masha",
    "db.options" => [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
    ],
];