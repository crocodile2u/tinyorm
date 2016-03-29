# tinyorm
Very minimalistic ORM &amp; DB tools for PHP

# Why yet another library?
I know quite a lot of similar projects but they all don't satisfy me. Therefore, I made a list of requirements that my perfect ORM library should meet.

* It should be tiny. I don't want to have tons of classes added to my next project just because I want to automate database routines.
* It should not be too smart. It should help me to perform ordinary tasks and stay as much under control as possible.
* It should not generate DB schema. For migrations I will use special instruments.
* No lazy-loading of related objects. No this magic, no behind-the-scene bullshit.
* No weird aliases in SQL like _SELECT * FROM \\model\\Foo JOIN \\model\\Bar ..._
* Thin entities that do not contain any buisiness logic or even the storage-specific logic (relations etc). This makes things overcomplicated and results in less control from the developer. Separation of persistence logic from the entities makes them reusable even when you switch storage backends. While this is not often needed, this is a handy feature which is easily achievable. Moreover, sometimes I would like, for example, to access the same entity in different ways: for example, if I have a table which I want to access via both MySQL &amp; Handlersocket.
* A Query-Object implementation with a neat interface. I usually prefer to write SQL queries by hand. However, there are cases when the query needs to be formed dynamically, and in such cases an object with clean interface is a great advantage over composing SQL string by hand.
* It should be able of handling multiple DB connections. Ideally it should have a transaction manager which issues BEGIN/COMMIT/ROLLBACK for all connections participating in DB interactions.
* Ideally it should have a scaffolding utility to generate entities from DB tables.

While there are tools that conform to some of the requirements, I failed to find a library that has it all.

# Show me the code!

Select usage:
```php
$select = (new Select("my_table"))
    ->join("LEFT JOIN another_table USING (join_column)")
    ->where("filter_column = ?", $filterColumnValue)
    ->setConnection($db);
$count = $select->count("DISTINCT id");
$rows = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
```

You can set fetch modes for Select:
```php
$select->setFetchMode(\PDO::FETCH_ASSOC);
$select->setFetchClass(MyTableEntity::class);
$select->setFetchInto(new MyTableEntity());
```

Working with multiple DB connections:

```php
$txManager = new \tinyorm\TxManager();
$txManager->registerConnection($this->connection)
    ->registerConnection($this->connection2);

$result = $txManager->atomic(function () {
    $this->connection->exec("INSERT INTO test (c_unique) VALUES ('val1')");
    $this->connection2->exec("INSERT INTO test (c_unique) VALUES ('val2')");
    return true;
});
```

This way, if anything goes wrong with the first or the second INSERT, transactions in both connection will be rolled back, no rows will be inserted. On the other hand, if everything goes fine, transactions in both connection will be commited.

Transaction manager supports nested transactions, and the tinyorm\Db class also supports them.

# The approach
I used an approach similar to that of Zend Framework 2 ( http://framework.zend.com/manual/current/en/user-guide/database-and-models.html ). The entity classes are just simple data containers that do not have DB connection/persistence logic. However, in the end, tinyorm entities do have minimal "knowledge" about their relationship to a storage layer. First, they have _getSourceName()_ method which essentially is meant to return a storage table/collection name. Second, there are _getPK()_ and _setPK()_ methods to access primary key. The name primary key column is stored in protected _pkName_ property. And finally, entities have _getSequenceName()_. I made all this for the sake of simplicity, in order not to have to introduce more classes. tinyorm only supports _AUTO_INCREMENT_'ed primary keys.
In contracts to Zend Framework 2, all the stuff related to persistence is just a few classes/interfaces:

* DbInterface - the interface for Db connector
* Db - a wrapper around PDO
* persistence\Driver - the interface for persistence driver
* persistence\DbDriver - persistence driver implementation with Db/PDO (thus RDBMS) as backend. Only tested with MySQL, though should also work fine with Postgres and Sqlite
* persistence\HsDriver - persistence driver implementation with handlersocket as backend.

Persistence driver operates on Entities. In case of ZF2, we can talk about their _Table Gateway_ as a persistence driver. See the link above for reference. In tinyorm, things are way more simple. You just create a persistence driver instance and call its' _save()_, _insert()_, _update()_, _delete()_ methods providing an Entity as an argument.

For a query object, I took a look at Phalcon framework ( https://docs.phalconphp.com/en/latest/api/Phalcon_Mvc_Model_Query_Builder.html ). However, I modified the interface a little, so I find it a little bit better.

I also implemented a database transaction manager capable of handling multiple DB connections.

# Credits
Thanks RasmiKanta Moharana (https://github.com/rashmi8105) for early feedback & spotting bugs in the example app! 