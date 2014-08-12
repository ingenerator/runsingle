<?php
/**
 * Database driver spec
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */


namespace spec\Ingenerator\RunSingle;

use PhpSpec\Exception\Example\FailureException;
use spec\ObjectBehavior;
use Prophecy\Argument;

class DbDriverSpec extends ObjectBehavior
{
    const INSERT_LOCK_SQL = "INSERT INTO locks VALUES(:task_name, :timestamp, :timeout)";
    const SELECT_LOCK_SQL = "SELECT * FROM locks WHERE task_name = :task_name AND (lock_timestamp + timeout) < :current_timestamp";
    const DELETE_LOCK_SQL = "DELETE FROM locks WHERE task_name = :task_name AND lock_timestamp = :lock_timestamp";

    const TASK_NAME = 'testscript';
    const TIMEOUT   = 10;

    const FAKE_TIMESTAMP = 1406628662;

    /**
     * Use $this->subject to get proper type hinting for the subject class
     * @var \Ingenerator\RunSingle\DbDriver
     */
    protected $subject;

    public static function faketime()
    {
        return self::FAKE_TIMESTAMP;
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     */
    function let($db_object)
    {
        $db_object->execute(self::INSERT_LOCK_SQL, array(
            ':task_name' => self::TASK_NAME,
            ':timeout'   => self::TIMEOUT,
            ':timestamp' => self::FAKE_TIMESTAMP
        ))->willReturn();
        $db_object->get_db_table_name()->willReturn('locks');
        $db_object->fetch_all(self::SELECT_LOCK_SQL, array(
            ':task_name'         => self::TASK_NAME,
            ':current_timestamp' => self::FAKE_TIMESTAMP
        ))->willReturn();

        $this->subject->beConstructedWith($db_object);
        $this->subject->set_time_provider(__CLASS__ . '::faketime');
    }

    function it_is_initializable()
    {
        $this->subject->shouldHaveType('Ingenerator\RunSingle\DbDriver');
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     */
    function its_get_lock_inserts_a_lock_if_none_already($db_object)
    {
        $this->subject->get_lock(self::TASK_NAME, 10, TRUE);

        $db_object->execute(self::INSERT_LOCK_SQL, array(
            ':task_name' => self::TASK_NAME,
            ':timeout'   => self::TIMEOUT,
            ':timestamp' => self::FAKE_TIMESTAMP
        ))
                  ->shouldHaveBeenCalled();
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     *
     * @throws \PhpSpec\Exception\Example\FailureException
     */
    function its_get_lock_rethrows_exception_on_insert_if_not_duplicate_key($db_object)
    {
        $db_object->execute(self::INSERT_LOCK_SQL, array(
            ':task_name' => self::TASK_NAME,
            ':timeout'   => self::TIMEOUT,
            ':timestamp' => self::FAKE_TIMESTAMP
        ))
                  ->willThrow(new \PDOException);
        try {
            $this->subject->get_lock('testscript', 10, TRUE);
            throw new FailureException("Expected exception not thrown");
        } catch (\PDOException $e) {
            // Expected
        }
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     */
    function its_get_lock_returns_false_if_insert_lock_throws_duplicate_key($db_object)
    {
        $db_object->execute(self::INSERT_LOCK_SQL, array(
            ':task_name' => self::TASK_NAME,
            ':timeout'   => self::TIMEOUT,
            ':timestamp' => self::FAKE_TIMESTAMP
        ))
                  ->willThrow(new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'testscript' for key 'PRIMARY'"));
        $this->subject->get_lock('testscript', 10, TRUE)->shouldBe(FALSE);
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     */
    function its_get_lock_returns_lock_timestamp_if_insert_lock_succeeds($db_object)
    {
        $db_object->execute(self::INSERT_LOCK_SQL, array(
            ':task_name' => self::TASK_NAME,
            ':timeout'   => self::TIMEOUT,
            ':timestamp' => self::FAKE_TIMESTAMP
        ))
                  ->willReturn(TRUE);
        $this->subject->get_lock('testscript', 10, TRUE)->shouldBe(self::FAKE_TIMESTAMP);
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     */
    function its_get_lock_should_run_garbage_collection($db_object)
    {
        $this->givenOldLockToGarbageCollect($db_object, self::TASK_NAME, self::FAKE_TIMESTAMP);
        $this->subject->get_lock(self::TASK_NAME, 10, TRUE);
        $this->shouldHaveReleasedLock($db_object, self::TASK_NAME, self::FAKE_TIMESTAMP);
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     */
    function its_garbage_collect_fetches_details_of_expired_locks($db_object)
    {
        $this->subject->garbage_collect(self::TASK_NAME, 10, 0);
        $this->shouldHaveQueriedForLocksToGarbageCollect($db_object, self::TASK_NAME);
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     */
    function its_garbage_collect_does_not_delete_if_no_expired_locks($db_object)
    {
        $this->subject->garbage_collect(self::TASK_NAME, 10, self::FAKE_TIMESTAMP);
        $db_object->execute(self::DELETE_LOCK_SQL, array(
            ':task_name'      => self::TASK_NAME,
            ':lock_timestamp' => self::FAKE_TIMESTAMP
        ))
                  ->shouldNotHaveBeenCalled();
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     */
    function its_garbage_collect_releases_expired_lock_if_existing($db_object)
    {
        $this->givenOldLockToGarbageCollect($db_object, self::TASK_NAME, self::FAKE_TIMESTAMP);
        $this->subject->garbage_collect(self::TASK_NAME, 10, self::FAKE_TIMESTAMP);
        $this->shouldHaveReleasedLock($db_object, self::TASK_NAME, self::FAKE_TIMESTAMP);
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     */
    function its_release_lock_releases_lock_with_id($db_object)
    {
        $this->givenOldLockToGarbageCollect($db_object, self::TASK_NAME, self::FAKE_TIMESTAMP);
        $this->subject->release_lock(self::TASK_NAME, self::FAKE_TIMESTAMP);
        $this->shouldHaveReleasedLock($db_object, self::TASK_NAME, self::FAKE_TIMESTAMP);
    }

    public function shouldHaveQueriedForLocksToGarbageCollect($db_object)
    {
        $db_object->fetch_all(self::SELECT_LOCK_SQL, array(
            ':task_name'         => self::TASK_NAME,
            ':current_timestamp' => self::FAKE_TIMESTAMP
        ))
                  ->shouldHaveBeenCalled();
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     * @param string                                   $task_name
     * @param int                                      $lock_timestamp
     */
    protected function shouldHaveReleasedLock($db_object, $task_name, $lock_timestamp)
    {
        $db_object->execute(self::DELETE_LOCK_SQL, array(
            ':task_name'      => $task_name,
            ':lock_timestamp' => $lock_timestamp
        ))
                  ->shouldHaveBeenCalled();
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     * @param string                                   $task_name
     * @param int                                      $lock_id
     */
    protected function givenOldLockToGarbageCollect($db_object, $task_name, $lock_id)
    {
        $db_object->fetch_all(self::SELECT_LOCK_SQL, array(
            ':task_name'         => self::TASK_NAME,
            ':current_timestamp' => self::FAKE_TIMESTAMP
        ))
                  ->willReturn(array(array('task_name' => $task_name, 'lock_timestamp' => $lock_id)));

        $db_object->execute(self::DELETE_LOCK_SQL, array(
            ':task_name'      => self::TASK_NAME,
            ':lock_timestamp' => self::FAKE_TIMESTAMP
        ))->willReturn();
    }

}
