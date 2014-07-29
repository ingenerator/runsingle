<?php
/**
 * Defines ArgumentParserSpec - specifications for Ingenerator\RunSingle\ArgumentParser
 *
 * @author     Matthias Gisder <matthias@ingenerator.com>
 * @copyright  2014 inGenerator Ltd
 * @licence    proprietary
 */

namespace spec\Ingenerator\RunSingle;

use PhpSpec\Exception\Example\FailureException;
use spec\ObjectBehavior;
use Prophecy\Argument;

/**
 *
 * @see Ingenerator\RunSingle\ArgumentParser
 */
class ArgumentParserSpec extends ObjectBehavior
{
    /**
     * Use $this->subject to get proper type hinting for the subject class
     * @var \Ingenerator\RunSingle\ArgumentParser
     */
	protected $subject;

    function it_throws_without_command()
    {
        $args = array(
            './run_single.php',
            '--task_name=testscript',
            '--timeout=10',
        );
        try {
            $result = $this->subject->parse($args);
            throw new FailureException("Expected exception not thrown");
        } catch (\InvalidArgumentException $e){
            // Expected
        }

    }

	function it_is_initializable()
    {
		$this->subject->shouldHaveType('Ingenerator\RunSingle\ArgumentParser');
	}

    function it_parses_timeout_from_commandline()
    {
        $args = array(
            './run_single.php',
            '--gc=0',
            '--task_name=testscript',
            '--timeout=10',
            '--',
            'php',
            'script.php',
        );
        $result = $this->subject->parse($args);

        expect($result['timeout']->getWrappedObject())->toBe('10');
    }

    function it_parses_command_from_commandline()
    {
        $args = array(
            './run_single.php',
            '--task_name=testscript',
            '--timeout=10',
            '--',
            'php',
            'script.php',
            '-o',
            '--opt=value',
            'name',
            '8',
            'additional_arg',
            '/some/directory with/spaces',
            '/some/directory',
            'with/spaces',
            'without',
            'quotes',
            '--',
            'gratuitous_stuff',
        );
        $result = $this->subject->parse($args);

        $result['command']->shouldBe("'php' 'script.php' '-o' '--opt=value' 'name' '8' 'additional_arg' '/some/directory with/spaces' '/some/directory' 'with/spaces' 'without' 'quotes' '--' 'gratuitous_stuff'");    }

    function it_parses_task_name_from_commandline()
    {
        $args = array(
            './run_single.php',
            '--task_name=testscript',
            '--timeout=10',
            '--',
            'php',
            'script.php',
        );
        $result = $this->subject->parse($args);

        expect($result['task_name']->getWrappedObject())->toBe('testscript');
    }

    function it_defaults_to_garbage_collect_active()
    {
        $args = array(
            './run_single.php',
            '--task_name=testscript',
            '--timeout=10',
            '--',
            'php',
            'script.php',
        );
        $result = $this->subject->parse($args);

        $result['automatic_garbage_collect']->shouldBe(TRUE);
    }

    function it_parses_no_garbage_collect_option_from_commandline()
    {
        $args = array(
            './run_single.php',
            '--gc=0',
            '--task_name=testscript',
            '--timeout=10',
            '--',
            'php',
            'script.php',
        );
        $result = $this->subject->parse($args);
        $result['automatic_garbage_collect']->shouldBe(FALSE);
    }

    function it_throws_without_task_name()
    {
        $args = array(
            './run_single.php',
            '--gc=0',
            '--timeout=10',
            '--',
            'php',
            'script.php',
        );
        try {
            $result = $this->subject->parse($args);
            throw new FailureException("Expected exception not thrown");
        } catch (\InvalidArgumentException $e){
            // Expected
        }
    }


    function it_throws_without_timeout()
    {
        $args = array(
            './run_single.php',
            '--gc=0',
            '--task_name=testscript',
            '--',
            'php',
            'script.php',
        );
        try {
            $result = $this->subject->parse($args);
            throw new FailureException("Expected exception not thrown");
        } catch (\InvalidArgumentException $e){
            // Expected
        }
    }
}
