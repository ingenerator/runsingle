# RunSingle

... is a locking wrapper to ensure a command is only being run once at any given time
across multiple instances.
Locking is done via a database on a remote host.

## Installing and building your database

Clone or download this repository, or just add it to your composer.json:

```json
{
  "require": {
    "ingenerator/RunSingle" : "0.*"
  }
}
```

You will need php.

## Initial DB configuration
For use with the standard driver, RunSingle needs access to a MySql database
with a table containing these fields:

```sql
CREATE TABLE IF NOT EXISTS locks (
  task_name varchar(255),
  lock_timestamp int,
  timeout int,
  PRIMARY KEY (task_name)
```

Create a file named 'run_single_config.php' in your project root on each instance.
This file should return a LockDriver. You can return the standard database driver 
by passing the credentials to the DbDriverFactory, e.g.:

```php
<?php
return Ingenerator\RunSingle\DbDriverFactory::factory(array(
    'db'             => 'mysql',
    'host'           => 'localhost',
    'db_user'        => 'root',
    'db_pass'        => '',
    'db_name'        => 'run_single_db',
    'db_table_name'  => 'locks',
));
```

## Running tasks

### Using the standard driver with the wrapper script provided:
```bash
bin/run_single.php [--no-garbage--collect] --task_name=<task_name> --timeout=<timeout_in_seconds> -- <command>
```

## Options:
```bash
--no-garbage-collect
```
Add this to have RunSingle not automatically garbage collect stale entries
from the lock storage.
This will usually make sense in highly concurrent setups.
Disable the garbage collection on all instances BUT ONE.
Having --no-garbage-collect on ALL instances means the lock will never be cleared.

## Parameters:
```bash
--task_name=<task_name>
```
value is the identifier for the lock.
Choose a unique, ideally short yet telling title (it has to be the same across all instances).

```bash
--timeout=<timeout_in_seconds>
```
The amount of time in seconds the lock will be granted.
It has to be bigger than the worst expected (longest) runtime of the command.
A good rule of thumb is to set this to the longest interval you can afford between running the command.
This strategy also serves to maximise confidence in the command really having completed.

```bash
 -- <command>
```
The command to be run. Make sure there is a space ON BOTH SIDES OF THE DOUBLE DASH preceding the command,
otherwise the wrapper cannot safely distinguish your command from its own arguments.
Command arguments will be automatically escaped, so simply type the command after the " -- " as you would do on the shell.

## Command output ...
is printed to STDOUT/STDERR as the actual script execution is done via system().

## Using the LockDriver in your own classes

```php
$lock_driver = new \Ingenerator\RunSingle\DbDriver;
$lock_id = $lock_driver->get_lock($task_name, $timeout, $garbage_collect);
if ($lock_id) {
// do your stuff
  $lock_driver->release_lock($task_name, $lock_id);
}
```

## Roll your own driver
In order to use another storage for the lock (different database system, memcached,
even file system), RunSingle will need to be passed an alternate driver
implementing the LockDriver interface.

Use run_single_config.php to return an instance of your driver
class (a factory is a handy way to do that but by no means mandatory).
