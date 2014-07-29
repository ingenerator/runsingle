<?php
/**
 * Argument parser
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   proprietary
 */


namespace Ingenerator\RunSingle;


class ArgumentParser {

    /**
     * @param array $argv
     * @return array
     * @throws \InvalidArgumentException
     */
    public function parse(array $argv)
    {
        $args = array();
        $sep_counter = 0;
        $arg_parts = array();

        foreach ($argv as $key => $value) {
            if ($value == '--') {
                break;
            }
            $sep_counter++;

            $new = explode('=', $value);

            if(count($new) > 1 && preg_match('/\-\-/', $new[0])) {
                $new[0] = str_replace('--', '', $new[0]);
                $arg_parts[$new[0]] = $new[1];
            }
        }

        if (!isset($arg_parts['task_name']) || !is_string($arg_parts['task_name'])) {
            throw(new \InvalidArgumentException('invalid or missing task_name value (set with "--task_name=".'));
        }
        $args['task_name'] = $arg_parts['task_name'];

        if (!isset($arg_parts['timeout']) || !is_int($arg_parts['timeout'] + 0)) {
            throw(new \InvalidArgumentException('invalid or missing --timeout value (set with "--timeout=".'));
        }
        $args['timeout'] = $arg_parts['timeout'];

        $args['automatic_garbage_collect'] = TRUE;
        if (isset($arg_parts['gc'])){

            $args['automatic_garbage_collect'] = (bool)$arg_parts['gc'];
        }

        $command_parts = array();
        if($sep_counter > 0) {
            $command_parts = array_slice($argv, $sep_counter + 1);
        }

        $command_parts = array_map('escapeshellarg', $command_parts);
        $args['command'] = implode(' ', $command_parts);
        if ($args['command'] === ''){
            throw(new \InvalidArgumentException('command has to be specified'));
        }

        return $args;
    }
}
