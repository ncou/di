# Phapi Dependency Injection Container

Phapi has a Dependency Injector Container that can be used to store both objects and parameters. It's easy to use and it gives the opportunity to replace many of the built in functionality in Phapi since Phapi uses the Container to store most of it's objects, dependencies and parameters.

The container can be accessed in an endpoint by using the <code>$this->app</code> parameter.

## Defining parameters
The easiest example is to store parameters in the container:
```php
<?php
// Set parameter
$di['param'] = 'value';

// Get parameter
echo $di['param']; // Output: value
```

## Defining objects and dependencies
Objects and its dependencies are defined by **anonymous functions** that returns an instance of an object, you have two options you can use to define them. Either by using:
```php
<?php
// Define database configuration
$di['dbUserName'] = 'root';
$di['dbPassword'] = '1234';

// Add DB connection to container
$di['dbConn'] = function ($app) {
  return new PDO(
    'mysql:host=localhost;dbname=test',
    $app['dbUserName'],
    $app['dbPassword']
  );
};
```

Or by using the <code>bind()</code> method:

```php
<?php
// Define database configuration
$di['dbUserName'] = 'root';
$di['dbPassword'] = '1234';

// Add DB connection to container
$di->bind('dbConn', function ($app) {
  return new PDO(
    'mysql:host=localhost;dbname=test',
    $app['dbUserName'],
    $app['dbPassword']
  );
});
```

Notice that the function has access to the container instance which makes it possible to fetch parameters and dependencies from the container.

By default the container will return the same instance of the object each time you get it. The <code>bind()</code> method takes a third optional parameter for defining if the **singleton** or **multiton** pattern should be used while creating the object. The default is singleton. Change it to multiton and each call to <code>$di['dbConn']</code> will now create and return a new instance.Example:

```php
<?php
// Define database configuration
$di['dbUserName'] = 'root';
$di['dbPassword'] = '1234';

// Add DB connection to container
$di->bind('dbConn', function ($app) {
  return new PDO(
    'mysql:host=localhost;dbname=test',
    $app['dbUserName'],
    $app['dbPassword']
  );
}, \Phapi::TYPE_MULTITON);
```

The order of the definitions doesn't matter since the objects are created when you get them.

## Retrieving object and/or parameters
Using the object is easy:
```php
<?php
// Use DB connection
$di['dbConn']->query('SELECT ...');
```

Or use the <code>make()</code> method:
```php
<?php
// Get DB connection
$db = $di->make('dbConn');
// Use the connection
$db->query('Select ...');
```

## Removing objects and/or parameters
The container implements the <code>\ArrayAccess</code> interface which makes it possible to use as a regular array. Removing an object or a parameter is just a matter of unsetting it:

```php
<?php
unset($di['dbConn']);
```

## Validators
In some cases you might want to ensure a certain key has a specific type of value. Phapi needs the <code>$di['log']</code> to be a PSR-3 compatible logger. To enforce this a validator is assigned to the **log** key.

```php
<?php
use Phapi\Container\Validator\Log;

$di->addValidator('log', new Log($this));
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
    return function ($app) {
        return new NullLogger();
    };
}
```

As you can see in this example we don't raise an error even if the provided logger is invalid. This is done since we don't want the application to break because of a faulty logger. In other cases it's probably more suitable to throw an exception.
