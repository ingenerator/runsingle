<?php
/**
 * Defines LockSpec - specifications for Ingenerator\RunSingle\Lock
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright  2014 inGenerator Ltd
 * @licence    BSD
 */

namespace spec\Ingenerator\RunSingle;

use spec\ObjectBehavior;
use Prophecy\Argument;

/**
 *
 * @see Ingenerator\RunSingle\Lock
 */
class LockSpec extends ObjectBehavior
{

    const FAKE_TIMESTAMP = 1406628662;

    const FAKE_LOCK_HOLDER = '127.0.0.1';

    /**
     * Use $this->subject to get proper type hinting for the subject class
     * @var \Ingenerator\RunSingle\Lock
     */
	protected $subject;

    function let()
    {
        $data = array();
        $this->subject->beConstructedWith($data);
    }

	function it_is_initializable()
    {
		$this->subject->shouldHaveType('Ingenerator\RunSingle\Lock');
	}

    function it_returns_task_name_for_lock()
    {
        $data = array(
            'task_name' => 'test task name',
        );
        $this->subject->beConstructedWith($data);
        $this->subject->get_task_name()->shouldBe('test task name');
    }

    function it_returns_lock_id_for_lock()
    {
        $data = array(
            'lock_id' => self::FAKE_TIMESTAMP,
        );
        $this->subject->beConstructedWith($data);
        $this->subject->get_lock_id()->shouldBe(self::FAKE_TIMESTAMP);
    }

    function it_returns_timeout_for_lock()
    {
        $data = array(
            'timeout' => 10,
        );
        $this->subject->beConstructedWith($data);
        $this->subject->get_timeout()->shouldBe(10);
    }

    function it_returns_lock_holder_for_lock()
    {
        $data = array(
            'lock_holder' => 'test lock holder',
        );
        $this->subject->beConstructedWith($data);
        $this->subject->get_lock_holder()->shouldBe('test lock holder');
    }

    function it_returns_expires_for_lock()
    {
        $datetime_expires = new \DateTime('@'.(self::FAKE_TIMESTAMP + 10));
        $data = array(
            'expires' => $datetime_expires,
        );
        $this->subject->beConstructedWith($data);
        $this->subject->get_expires()->shouldBe($datetime_expires);
    }

    function it_returns_locked_at_for_lock()
    {
        $datetime_locked_at = new \DateTime('@'.(self::FAKE_TIMESTAMP));
        $data = array(
            'locked_at' => $datetime_locked_at,
        );
        $this->subject->beConstructedWith($data);
        $this->subject->get_locked_at()->shouldBe($datetime_locked_at);
    }

    function it_returns_lock_description()
    {
        $datetime_locked_at = new \DateTime('@'.(self::FAKE_TIMESTAMP));
        $datetime_expires = new \DateTime('@'.(self::FAKE_TIMESTAMP + 10));
        $data = array(
            'task_name' => 'test task_name',
            'lock_id' => self::FAKE_TIMESTAMP,
            'timeout'   => 10,
            'lock_holder' => self::FAKE_LOCK_HOLDER,
            'expires' => $datetime_expires,
            'locked_at' => $datetime_locked_at,
        );

        $this->subject->beConstructedWith($data);

        $this->subject->__toString()->shouldBe('Lock 1406628662 for task test task_name taken by 127.0.0.1 at 29/07/2014 10:11:02 with timeout 10 expires at 29/07/2014 10:11:12.');
    }

}
