
Postman
=================================

A Php based Mysql Prepare Statement Wrapper Class.
(Supports list statement, ex. insert multiple items with a single prepare statement call.)

Installation
============
1. Drop the 'system/Postman.php' library into your libraries directory.
2. include/require the Postman.php file where you need to make database actions.

Usage
=====
**Make Connection**

Because we always want a single connection, we must call `Postman::getInstance();` function. Fill in the host, userid, password and database to use. *Check test_case.php line 10*.

	$postman = Postman::getInstance( $host, $userid, $password, $database );

**Execute Query**
To make simple sql executions, call the `$postman->execute()` function directly. *Check test_case.php line 45*.

	$postman->execute(/* MySQL Query with ? in it. */, /* Two dimentional array holding fmt and values to insert */);

**Get List**
If you need to return a list of data, do not call `$postman->execute()` directly, call `$postman->executeList()` fuction. *Check test_case.php line 87*.

    $list = $this->postman->executeList(/* MySQL Query with ? in it. */, /* Two dimentional array holding fmt and values to insert */);


Help
====
For more information on how to use Mysql Prepare Statement then please visit: [http://php.net/manual/en/mysqli.quickstart.prepared-statements.php](http://php.net/manual/en/mysqli.quickstart.prepared-statements.php)

License
=======
* Apache License
