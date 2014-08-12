<?php
/**
 * Command runner
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */


namespace Ingenerator\RunSingle;


class CommandRunner
{

    /**
     * @param string $command
     *
     * @return int
     */
    public function execute($command)
    {
        system($command, $exit_code);
        return $exit_code;
    }
}
