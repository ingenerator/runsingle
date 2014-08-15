<?php
/**
 * Defines LockSpec - specifications for Ingenerator\RunSingle\Lock
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

    function it_returns_lock_description()
    {
        $data = array(
            'task_name'   => 'test task_name',
            'lock_id'     => self::FAKE_TIMESTAMP,
            'timeout'     => 10,
            'lock_holder' => self::FAKE_LOCK_HOLDER,
            'expires'     => new \DateTime('@'.(self::FAKE_TIMESTAMP + 10)),
            'locked_at'   => new \DateTime('@'.(self::FAKE_TIMESTAMP)),
        );

        $this->subject->beConstructedWith($data);

        $this->subject->__toString()
                      ->shouldBe('Lock 1406628662 for task test task_name taken by 127.0.0.1 at 29/07/2014 10:11:02 with timeout 10 expires at 29/07/2014 10:11:12.');
    }

}
