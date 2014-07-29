<?php
# copy this file into your working directory and edit the credentials.
# If you would like to roll your own database driver, return it here.

return Ingenerator\RunSingle\DbDriverFactory::factory(array(
    'db'             => 'mysql',
    'host'           => 'localhost',
    'db_user'        => 'root',
    'db_pass'        => '',
    'db_name'        => 'run_single_db',
    'db_table_name'  => 'locks',
));
