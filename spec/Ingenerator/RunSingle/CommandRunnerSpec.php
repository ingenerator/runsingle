<?php
/**
 * Defines CommandRunnerSpec - specifications for Ingenerator\RunSingle\CommandRunner
 *
 * @author     Matthias Gisder <matthias@ingenerator.com>
 * @copyright  2014 inGenerator Ltd
 * @licence    proprietary
 */

namespace spec\Ingenerator\RunSingle;

use spec\ObjectBehavior;
use Prophecy\Argument;

/**
 *
 * @see Ingenerator\RunSingle\CommandRunner
 */
class CommandRunnerSpec extends ObjectBehavior
{
    /**
     * Use $this->subject to get proper type hinting for the subject class
     * @var \Ingenerator\RunSingle\CommandRunner
     */
	protected $subject;

	function it_is_initializable()
    {
		$this->subject->shouldHaveType('Ingenerator\RunSingle\CommandRunner');
	}

    function it_returns_0_on_successful_command()
    {
        $tmpdir = sys_get_temp_dir();
        $this->subject->execute('ls '.escapeshellarg($tmpdir) . " > /dev/null")->shouldBe(0);
    }

    function it_returns_nonzero_on_failing_command()
    {
        do {
            $non_dir = sys_get_temp_dir().'/'.uniqid();
        } while (file_exists($non_dir));

        $this->subject->execute('ls '.escapeshellarg($non_dir) . " 2> /dev/null")->shouldNotBe(0);
        $this->subject->execute('ls '.escapeshellarg($non_dir) . " 2> /dev/null")->shouldBe(2);
    }

    function it_runs_provided_command()
    {
        $script = __DIR__.'/test-execution.sh';
        $handle = fopen($script, 'w');
        $file_content = <<< 'EOF'
#! /bin/bash
# Call this like
# test-execution.sh path/to/tmpfile some argument "with args"
for arg in "$@"
  do
    echo $arg >> $1;
  done
EOF;
        fwrite($handle, $file_content);
        fclose($handle);
        chmod($script, 0755);
        $tmpfile = tempnam(sys_get_temp_dir(), 'command-test_');
        $cmd = $script.' '.escapeshellarg($tmpfile).' some "argument with" arguments';
        $this->subject->execute($cmd);

        $received_args = file_get_contents($tmpfile);
        $expected = <<<ARGS
$tmpfile
some
argument with
arguments

ARGS;

        expect($received_args)->toBe($expected);
        unlink($tmpfile);
        unlink($script);
    }

    function it_outputs_error_messages()
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'command-test_');
        $non_file = "";
        do {
            $non_file = sys_get_temp_dir().'/'.uniqid();
        } while (file_exists($non_file));
        $cmd = 'ls ' . $non_file . " 2> $tmpfile";
        $this->subject->execute($cmd);
        $received_output = file_get_contents($tmpfile);

        $expected = <<<"OUTPUT"
ls: cannot access $non_file: No such file or directory

OUTPUT;
        expect($received_output)->toBe($expected);
        unlink($tmpfile);
    }
}
