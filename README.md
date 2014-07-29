###RunSingle

... is a wrapper to ensure the script is only being run once across multiple instances.
Locking is done via a database on a remote host.

###Configuration
  - Download it.
  - Copy the file run_single_config.php to your working directory.
    Edit it to contain the actual credentials to the host and database you would like to use.

###Initial DB configuration
  Call create_db.php to have RunSingle create the initial database for you.
  Make sure to place run_single_config.php in your working directory.

###Instantiation directly from your module
Using the factory provided, it is as easy as:

```php
    $runsingle = \Ingenerator\RunSingle\Factory::create();
    $runsingle->execute('<task name>', '<task>', <timeout>, <garbage_collect>);
```

Or, from a script (see run_single.php):

```php
    $runsingle = \Ingenerator\RunSingle\Factory::create();
    $parser = new ArgumentParser;
    $args = $parser->parse($argv);
    $runsingle->execute($args['task_name'], $args['command'], $args['timeout'], $args['automatic_garbage_collect']);
```

###Call the wrapper from the shell

```php
    php ./run_single.php --gc=1 --task_name=test --timeout=10 -- ls -l
```

OR

```php
    php ./run_single.php --gc=0 --task_name=test --timeout=10 -- ls -l
```

###Garbage collection
Either ...

```php
    $runsingle->execute($args['task_name'], $args['command'], $args['timeout'], TRUE);
```

Or:

```php
    $runsingle->execute('<task name>', '<task>', <timeout>, FALSE);
```

and call garbage_collect() on the driver:
  
```php
  $driver->garbage_collect('<task_name>', '<timeout>')
```

###Command output ...
  is printed to STDOUT/STDERR as the actual script execution is done via system().

###Rolling your own database driver
  To use your DB of choice, implement the LockDriver interface.
  Use run_single_config.php to return an instance of your DbDriver
  class (a factory is a handy way to do that but by no means mandatory).
