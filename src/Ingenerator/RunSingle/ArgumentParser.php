<?php
/**
 * Argument parser
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */


namespace Ingenerator\RunSingle;


class ArgumentParser
{
    protected $sep_counter;

    protected $arg_defaults = array(
        'task_name'                 => '',
        'timeout'                   => '',
        'automatic_garbage_collect' => TRUE,
        'command'                   => '',
    );

    /**
     * @param array $argv
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function parse(array $argv)
    {
        $this->sep_counter   = $this->find_sep_counter($argv);
        $arg_parts           = \array_merge($this->arg_defaults, $this->find_arg_parts($argv));
        $this->command_parts = $this->escaped_command_parts($argv);

        $args['timeout']                   = $this->timeout($arg_parts);
        $args['task_name']                 = $this->task_name($arg_parts);
        $args['automatic_garbage_collect'] = $this->automatic_garbage_collect($arg_parts);
        $args['command']                   = $this->command($argv);

        return ($args);
    }

    /**
     * @param array $args
     *
     * @return int
     * @throws \InvalidArgumentException
     */
    protected function timeout($args)
    {
        if (! \is_numeric($args['timeout']) || $args['timeout'] <= 0) {
            throw new \InvalidArgumentException('invalid or missing timeout value (set with "--timeout=".');
        }

        return $args['timeout'];
    }

    /**
     * @param array $args
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function task_name($args)
    {
        if (! \is_string($args['task_name']) || $args['task_name'] == '') {
            throw new \InvalidArgumentException('invalid or missing task_name value (set with "--task_name=".');
        }

        return $args['task_name'];
    }

    /**
     * @param array $args
     *
     * @return bool
     */
    protected function automatic_garbage_collect($args)
    {
        if (isset($args['no-garbage-collect'])) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @param $argv
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function command($argv)
    {
        $command = \implode(' ', $this->escaped_command_parts($argv));
        if ($command === '') {
            throw new \InvalidArgumentException('command has to be specified');
        }

        return $command;
    }

    /**
     * @param array $argv
     *
     * @return array
     */
    protected function find_sep_counter($argv)
    {
        $sep_counter = 0;
        foreach ($argv as $arg) {
            if ($arg == '--') {
                break;
            }
            $sep_counter ++;
        }

        return $sep_counter;
    }

    /**
     * @param array $argv
     *
     * @return array
     */
    protected function find_arg_parts($argv)
    {
        $arg_parts         = array();
        $key_value_strings = $this->key_value_strings($argv);
        foreach ($key_value_strings as $key_value_string) {
            $new = \explode('=', $key_value_string);

            if (\preg_match('/\-\-/', $new[0])) {
                $new[0] = \str_replace('--', '', $new[0]);
                // for arguments requiring no value assignment
                if (! isset($new[1])) {
                    $new[1] = TRUE;
                }
                $arg_parts[$new[0]] = $new[1];
            }
        }

        return $arg_parts;
    }

    /**
     * @param array $argv
     *
     * @return array
     */
    protected function key_value_strings($argv)
    {
        $key_value_strings = array();
        if ($this->sep_counter > 0) {
            $key_value_strings = \array_slice($argv, 0, $this->sep_counter);
        }

        return $key_value_strings;
    }

    /**
     * @param array $argv
     *
     * @return array
     */
    protected function escaped_command_parts($argv)
    {
        $command_parts = array();
        if ($this->sep_counter > 0) {
            $command_parts = \array_slice($argv, $this->sep_counter + 1);
        }

        return \array_map('escapeshellarg', $command_parts);
    }

}
