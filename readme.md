# Dependency Injection Container

[![Build status](https://img.shields.io/travis/phapi/di.svg?style=flat-square)](https://travis-ci.org/phapi/di)
[![Code Climate](https://img.shields.io/codeclimate/github/phapi/di.svg?style=flat-square)](https://codeclimate.com/github/phapi/di)
[![Test Coverage](https://img.shields.io/codeclimate/coverage/github/phapi/di.svg?style=flat-square)](https://codeclimate.com/github/phapi/di/coverage)

Phapi has a built in Dependency Injector Container that can be used to store both objects and parameters. It's easy to use and it gives the opportunity to replace many of the built in functionalities since Phapi uses the Container to store most of it's objects, dependencies and parameters.

The container can be accessed in an endpoint by using the <code>$this->container</code> parameter.

## Installation
The package is installed by default by the Phapi framework. Installing the package to use is separately can be done by using composer:

```shell
$ composer require phapi/di:1.*
```



## Defining parameters
The easiest example is to store parameters in the container:
```php
<?php
// Set parameter
$container['param'] = 'value';

// Get parameter
echo $container['param']; // Output: value
```

## Defining objects and dependencies
Objects and its dependencies are defined by **anonymous functions** that returns an instance of an object, you have two options you can use to define them. Either by using:
```php
<?php
// Define database configuration
$container['dbUserName'] = 'root';
$container['dbPassword'] = '1234';

// Add DB connection to container
$container['dbConn'] = function ($container) {
  return new PDO(
    'mysql:host=localhost;dbname=test',
    $container['dbUserName'],
    $container['dbPassword']
  );
};
```

Or by using the <code>bind()</code> method:

```php
<?php
// Define database configuration
$container['dbUserName'] = 'root';
$container['dbPassword'] = '1234';

// Add DB connection to container
$container->bind('dbConn', function ($container) {
  return new PDO(
    'mysql:host=localhost;dbname=test',
    $container['dbUserName'],
    $container['dbPassword']
  );
});
```

Notice that the function has access to the container instance which makes it possible to fetch parameters and dependencies from the container.

By default the container will return the same instance of the object each time you get it. The <code>bind()</code> method takes a third optional parameter for defining if the **singleton** or **multiton** pattern should be used while creating the object. The default is singleton. Change it to multiton and each call to <code>$container['dbConn']</code> will now create and return a new instance.Example:

```php
<?php
// Define database configuration
$container['dbUserName'] = 'root';
$container['dbPassword'] = '1234';

// Add DB connection to container
$container->bind('dbConn', function ($container) {
  return new PDO(
    'mysql:host=localhost;dbname=test',
    $container['dbUserName'],
    $container['dbPassword']
  );
}, \Phapi::TYPE_MULTITON);
```

The order of the definitions doesn't matter since the objects are created when you get them.

## Retrieving objects and/or parameters
Using the object is easy:
```php
<?php
// Use DB connection
$container['dbConn']->query('SELECT ...');
```

Or use the <code>make()</code> method:
```php
<?php
// Get DB connection
$db = $container->make('dbConn');
// Use the connection
$db->query('Select ...');
```

## Removing objects and/or parameters
The container implements the <code>\ArrayAccess</code> interface which makes it possible to use as a regular array. Removing an object or a parameter is just a matter of unsetting it:

```php
<?php
unset($container['dbConn']);
```

## Validators
In some cases you might want to ensure a certain key has a specific type of value. Phapi needs the <code>$container['log']</code> to be a PSR-3 compatible logger. To enforce this a validator is assigned to the **log** key.

```php
<?php
// Register validator
$container->addValidator('log', new \Phapi\Container\Validator\Log($this));
```

The validators <code>validate()</code> method is a simple check if the provided logger is PSR-3 compatible:

```php
<?php
/**
 * Validates the configured logger. If no logger is configured or
 * if the configured logger isn't PSR-3 compliant an instance of
 * NullLogger will be used instead.
 *
 * The PSR-3 package includes a NullLogger that doesn't do
 * anything with the input but it also prevents the application
 * from failing.
 *
 * This simplifies the development since we don't have to check
 * if there actually are a valid cache to use. We can just ask
 * the Cache (even if its a NullCache) and we will get a response.
 */
public function validate($logger)
{
    $original = $logger;

    if (is_callable($logger)) {
        $logger = $logger($this->container);
    }

    // Check if logger is an instance of the PSR-3 logger interface
    if ($logger instanceof LoggerInterface) {
        return $original;
    }

    // A PSR-3 compatible log writer hasn't been configured so we
    // don't know if it is compatible with Phapi. Therefore we
    // create an instance of the NullLogger instead
    return function ($container) {
        return new NullLogger();
    };
}
```

As you can see in this example we don't raise an error even if the provided logger is invalid. This is done since we don't want the application to break because of a faulty logger. In other cases it's probably more suitable to throw an exception.

## License
Phapi Di Container is licensed under the MIT License - see the [license.md](https://github.com/phapi/di/blob/master/license.md) file for details

## Contribute
Contribution, bug fixes etc are [always welcome](https://github.com/phapi/di/issues/new).
