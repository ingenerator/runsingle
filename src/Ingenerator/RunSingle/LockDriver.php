<?php
/**
* LockDriver interface
*
* @author    Matthias Gisder <matthias@ingenerator.com>
* @copyright 2014 inGenerator Ltd
* @licence   proprietary
*/

namespace Ingenerator\RunSingle;

interface LockDriver
{
    public function __construct($db_object);

    public function init();

    public function get_lock($task_name, $timeout, $garbage_collect);

    public function garbage_collect($task_name, $timeout);

    public function release_lock($task_name, $lock_timestamp);

}

