# tinyorm
Very minimalistic ORM &amp; DB tools for PHP

# Why yet another library?
I know quite a lot of similar projects but they all don't satisfy me. Therefore, I made a list of requirements that my perfect ORM library should meet.

* It should be tiny. I don't want to have tons of classes added to my next project just because I want to automate database routines.
* It should not be too smart. It should help me to perform ordinary tasks and stay as much under control as possible.
* It should not generate DB schema. For migrations I will use special instruments.
* No lazy-loading of related objects. No this magic, no behind-the-scene bullshit.
* Thin entities that do not contain any buisiness logic or even the storage-specific logic (relations etc). This makes things overcomplicated and results in less control from the developer. Separation of persistence logic from the entities makes them reusable even when you switch storage backends. While this is not often needed, this is a handy feature which is easily achievable. Moreover, sometimes I would like, for example, to access the same entity in different ways: for example, I have a table which I can access via both MySQL &amp; Handlersocket.
* A Query-Object implementation with a neat interface.
* 