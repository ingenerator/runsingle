<?php
/**
*
*
* @author    Matthias Gisder <matthias@ingenerator.com>
* @copyright 2014 inGenerator Ltd
* @licence   proprietary
*/

namespace RunSingle;

interface LockDriver
{
    public function get_lock($task_name, $timeout);

    public function release_lock($task_name);

    public function garbage_collect($task_name, $timeout);

}

