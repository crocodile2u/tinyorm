<?php

return [
    "db" => [
        "dsn" => "mysql:host=localhost;dbname=todo_list;",
        "user" => "root",
        "password" => "masha",
        "options" => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ]
    ]
];