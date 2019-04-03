<?php
/**
 * Console logger spec
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */


namespace spec\Ingenerator\RunSingle;

use Psr\Log\LogLevel;
use spec\ObjectBehavior;
use Prophecy\Argument;

class ConsoleLoggerSpec extends ObjectBehavior
{
    /**
     * Use $this->subject to get proper type hinting for the subject class
     * @var \Ingenerator\RunSingle\ConsoleLogger
     */
    protected $subject;

    function it_is_initializable()
    {
        $this->subject->shouldHaveType('Ingenerator\RunSingle\ConsoleLogger');
    }

    function it_prepends_iso8601_timestamp_to_log()
    {
        $content = $this->given_logged(LogLevel::INFO, 'test');
        list ($timestamp, $message) = \explode(': ', $content);
        $timestamp = \DateTime::createFromFormat('Y-m-d\TH:i:se', $timestamp);
        expect($timestamp)->toBeAnInstanceOf('\DateTime');
        expect((\time() - $timestamp->getTimestamp()) <= 1)->toBe(TRUE);
    }

    function it_prepends_log_level_to_log()
    {
        $content = $this->given_logged(LogLevel::INFO, 'test');
        expect($content)->toMatch('/^[^ ]+ \[info\] /');

    }

    function it_logs_message_after_timestamp_and_loglevel()
    {
        $content = $this->given_logged(LogLevel::INFO, 'test');
        expect($content)->toMatch('/^[^ ]+ [^ ]+ test\n$/');
    }

    function given_logged($level, $message, $context = array())
    {
        \ob_start();
        $this->subject->log($level, $message, $context);
        return \ob_get_clean();
    }

}
