<?php
/**
 * Defines RunSingleSpec - specifications for Ingenerator\RunSingle\RunSingle
 *
 * @author     Matthias Gisder <matthias@ingenerator.com>
 * @copyright  2014 inGenerator Ltd
 * @licence    BSD
 */

namespace spec\Ingenerator\RunSingle;

use spec\ObjectBehavior;
use Prophecy\Argument;

/**
 *
 * @see Ingenerator\RunSingle\RunSingle
 */
class RunSingleSpec extends ObjectBehavior
{
    const TASK_NAME = 'testscript';
    const COMMAND   = 'testscript.sh';
    const TIMEOUT   = 10;

    /**
     * Use $this->subject to get proper type hinting for the subject class
     * @var \Ingenerator\RunSingle\RunSingle
     */
    protected $subject;

    /**
     * @param \Ingenerator\RunSingle\LockDriver    $driver
     * @param \Ingenerator\RunSingle\CommandRunner $runner
     * @param \Ingenerator\RunSingle\ConsoleLogger $logger
     * @param \Ingenerator\RunSingle\LockHolder  $lock_holder
     */
    function let($driver, $runner, $logger, $lock_holder)
    {
        $driver->garbage_collect(Argument::any())->willReturn();
        $this->subject->beConstructedWith($driver, $runner, $logger, $lock_holder);
        $driver->get_lock(self::TASK_NAME, self::TIMEOUT, Argument::type('string'))
               ->willReturn(FALSE);
        $lock_holder->get_lock_holder()->willReturn('test');
    }

    function it_is_initializable()
    {
        $this->subject->shouldHaveType('Ingenerator\RunSingle\RunSingle');
    }

    /**
     * @param \Ingenerator\RunSingle\LockDriver $driver
     * @param \Ingenerator\RunSingle\LockHolder $lock_holder
     */
    function it_tries_to_get_lock_for_requested_task_and_passes_timeout($driver, $lock_holder)
    {
        $lock_holder->get_lock_holder()->willReturn('test');
        $this->subject->execute(self::TASK_NAME, self::COMMAND, self::TIMEOUT, TRUE);
        $driver->get_lock(self::TASK_NAME, self::TIMEOUT, 'test')->shouldHaveBeenCalled();
    }

    /**
     * @param \Ingenerator\RunSingle\LockDriver $driver
     * @param \Ingenerator\RunSingle\LockHolder $lock_holder
     */
    function it_passes_garbage_collect_option_to_get_lock_method($driver, $lock_holder)
    {
        $lock_holder->get_lock_holder()->willReturn('test');
        $this->subject->execute(self::TASK_NAME, self::COMMAND, self::TIMEOUT, FALSE);
        $driver->get_lock(self::TASK_NAME, self::TIMEOUT, 'test')->shouldHaveBeenCalled();
    }

    /**
     * @param \Ingenerator\RunSingle\LockDriver   $driver
     */
    function it_returns_zero_if_lock_not_available($driver)
    {
        $driver->get_lock(self::TASK_NAME, self::TIMEOUT, Argument::type('string'))
               ->willReturn(FALSE);
        $this->subject->execute(self::TASK_NAME, self::COMMAND, self::TIMEOUT, TRUE)->shouldBe(0);
    }

    /**
     * @param \Ingenerator\RunSingle\LockDriver    $driver
     * @param \Ingenerator\RunSingle\CommandRunner $runner
     */
    function it_runs_task_and_returns_exit_code_if_task_ran($driver, $runner)
    {
        $this->given_lock_is_available($driver, self::TASK_NAME, self::TIMEOUT, 1426828665);
        $runner->execute(self::COMMAND)->willReturn(99);
        $this->subject->execute(self::TASK_NAME, self::COMMAND, self::TIMEOUT, TRUE)->shouldBe(99);
        $runner->execute(self::COMMAND)->shouldHaveBeenCalled();
    }

    /**
     * @param \Ingenerator\RunSingle\LockDriver $driver
     */
    function it_releases_lock_if_it_got_one($driver)
    {
        $this->given_lock_is_available($driver, self::TASK_NAME, self::TIMEOUT, 1426828665);
        $this->subject->execute(self::TASK_NAME, self::COMMAND, self::TIMEOUT, TRUE);
        $driver->release_lock(self::TASK_NAME, 1426828665)->shouldHaveBeenCalled();
    }

    /**
     * @param \Ingenerator\RunSingle\LockDriver    $driver
     * @param \Ingenerator\RunSingle\ConsoleLogger $logger
     */
    function it_logs_control_flow_if_lock_available($driver, $logger)
    {
        $this->given_lock_is_available($driver, self::TASK_NAME, self::TIMEOUT, 1426828665);
        $this->subject->execute(self::TASK_NAME, self::COMMAND, self::TIMEOUT, TRUE);
        $logger->info(Argument::type('string'))->shouldHaveBeenCalledTimes(7);
    }

    /**
     * @param \Ingenerator\RunSingle\LockDriver    $driver
     * @param \Ingenerator\RunSingle\ConsoleLogger $logger
     */
    function it_logs_control_flow_if_no_lock_available($driver, $logger)
    {
        $this->given_no_lock_is_available($driver, self::TASK_NAME, self::TIMEOUT, 1426828665);
        $this->subject->execute(self::TASK_NAME, self::COMMAND, self::TIMEOUT, TRUE);
        $logger->info(Argument::type('string'))->shouldHaveBeenCalledTimes(4);
    }

    /**
     * @param \Ingenerator\RunSingle\LockDriver $driver
     */
    function it_calls_garbage_collect_on_execute($driver)
    {
        $this->given_lock_is_available($driver, self::TASK_NAME, self::TIMEOUT, 1426828665);
        $this->subject->execute(self::TASK_NAME, self::COMMAND, self::TIMEOUT, TRUE);
        $driver->garbage_collect(self::TASK_NAME)->shouldHaveBeenCalledTimes(1);
    }


    /**
     * @param \Ingenerator\RunSingle\LockDriver $driver
     * @param string                            $task_name
     * @param int                               $timeout
     * @param int                               $lock_id
     */
    public function given_lock_is_available($driver, $task_name, $timeout, $lock_id)
    {
        $driver->get_lock($task_name, $timeout, Argument::type('string'))
               ->willReturn($lock_id);
        $driver->release_lock($task_name, $lock_id)->willReturn();
    }

    /**
     * @param \Ingenerator\RunSingle\LockDriver $driver
     * @param string                            $task_name
     * @param int                               $timeout
     */
    public function given_no_lock_is_available($driver, $task_name, $timeout)
    {
        $driver->get_lock($task_name, $timeout, Argument::type('string'))
               ->willReturn(FALSE);
    }
}
