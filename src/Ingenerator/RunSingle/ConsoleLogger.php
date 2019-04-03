<?php

namespace Ingenerator\RunSingle;

class ConsoleLogger extends \Psr\Log\AbstractLogger
{
    const DATE_FORMAT_INTL = 'Y-m-d\TH:i:se';

     /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $time = new \DateTime;
        echo \sprintf(
            '%s: [%s] %s'.\PHP_EOL,
            $time->format('c'),
            $level,
            $message
        );
    }

}
