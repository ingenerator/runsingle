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
    const INSERT_LOCK_SQL = "INSERT INTO locks VALUES(:task_name, :timestamp, :timeout, :lock_holder)";
    const SELECT_LOCK_SQL = "SELECT * FROM locks";
    const DELETE_LOCK_SQL = "DELETE FROM locks WHERE task_name = :task_name AND lock_timestamp = :lock_timestamp";

    const TASK_NAME = 'testscript';
    const TIMEOUT   = 10;

    const FAKE_TIMESTAMP = 1406628662;

    const FAKE_LOCK_HOLDER = '127.0.0.1';

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
     * @param \Psr\Log\LoggerInterface                 $logger
     */
    function let($db_object, $logger)
    {
        $db_object->execute(self::INSERT_LOCK_SQL, array(
            ':task_name'   => self::TASK_NAME,
            ':timeout'     => self::TIMEOUT,
            ':timestamp'   => self::FAKE_TIMESTAMP,
            ':lock_holder' => self::FAKE_LOCK_HOLDER
        ))->willReturn();
        $db_object->get_db_table_name()->willReturn('locks');
        $db_object->fetch_all(self::SELECT_LOCK_SQL, array())->willReturn();

        $this->subject->beConstructedWith($db_object);
        $logger->debug(Argument::any())->willReturn();
        $logger->warning(Argument::any())->willReturn();
        $this->subject->set_time_provider(__CLASS__.'::faketime');
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
        $this->subject->get_lock(self::TASK_NAME, 10, self::FAKE_LOCK_HOLDER);

        $db_object->execute(self::INSERT_LOCK_SQL, array(
            ':task_name'   => self::TASK_NAME,
            ':timeout'     => self::TIMEOUT,
            ':timestamp'   => self::FAKE_TIMESTAMP,
            ':lock_holder' => self::FAKE_LOCK_HOLDER
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
            ':task_name'   => self::TASK_NAME,
            ':timeout'     => self::TIMEOUT,
            ':timestamp'   => self::FAKE_TIMESTAMP,
            ':lock_holder' => self::FAKE_LOCK_HOLDER
        ))
                  ->willThrow(new \PDOException);
        try {
            $this->subject->get_lock('testscript', 10, self::FAKE_LOCK_HOLDER);
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
            ':task_name'   => self::TASK_NAME,
            ':timeout'     => self::TIMEOUT,
            ':timestamp'   => self::FAKE_TIMESTAMP,
            ':lock_holder' => self::FAKE_LOCK_HOLDER
        ))
                  ->willThrow(new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'testscript' for key 'PRIMARY'"));
        $this->subject->get_lock('testscript', 10, self::FAKE_LOCK_HOLDER)->shouldBe(FALSE);
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
        $this->subject->get_lock('testscript', 10, self::FAKE_LOCK_HOLDER)->shouldBe(self::FAKE_TIMESTAMP);
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
        $db_object->fetch_all(self::SELECT_LOCK_SQL, array())
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
     * @param \Ingenerator\RunSingle\ConsoleLogger $logger
     */
    function its_garbage_collect_logs_debug_if_no_lock_found($logger)
    {
        $this->subject->set_logger($logger);
        $this->subject->garbage_collect(self::TASK_NAME, 10, self::FAKE_TIMESTAMP);
        $logger->debug(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     * @param \Ingenerator\RunSingle\ConsoleLogger     $logger
     */
    function its_garbage_collect_logs_warning_if_lock_found($db_object, $logger)
    {
        $this->subject->set_logger($logger);
        $this->givenOldLockToGarbageCollect($db_object, self::TASK_NAME, self::FAKE_TIMESTAMP);
        $this->subject->garbage_collect(self::TASK_NAME, 10, self::FAKE_TIMESTAMP);
        $logger->warning(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     * @param \Ingenerator\RunSingle\ConsoleLogger     $logger
     */
    function its_release_lock_logs_debug($db_object, $logger)
    {
        $this->subject->set_logger($logger);
        $this->givenOldLockToGarbageCollect($db_object, self::TASK_NAME, self::FAKE_TIMESTAMP);
        $this->subject->release_lock(self::TASK_NAME, self::FAKE_TIMESTAMP);
        $logger->debug(Argument::any())->shouldHaveBeenCalled();
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     * @param \Ingenerator\RunSingle\ConsoleLogger     $logger
     */
    function it_does_not_log_if_logger_not_set($db_object, $logger)
    {
        $this->givenOldLockToGarbageCollect($db_object, self::TASK_NAME, self::FAKE_TIMESTAMP);
        $this->subject->release_lock(self::TASK_NAME, self::FAKE_TIMESTAMP);
        $logger->debug(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_builds_lock_object_from_query_result()
    {
        $query_result_data = array(
            'task_name'      => 'test',
            'lock_timestamp' => self::FAKE_TIMESTAMP,
            'timeout'        => 10,
            'lock_holder'    => self::FAKE_LOCK_HOLDER,
        );
        $lock_obj = $this->subject->build_lock_object($query_result_data);

        $lock_obj->get_task_name()->shouldBe('test');
        $lock_obj->get_lock_id()->shouldBe(self::FAKE_TIMESTAMP);
        $lock_obj->get_timeout()->shouldBe(10);
        $lock_obj->get_lock_holder()->shouldBe(self::FAKE_LOCK_HOLDER);
        $lock_obj->get_expires()->shouldBeLike(new \DateTime('@'.(self::FAKE_TIMESTAMP + 10)));
        $lock_obj->get_locked_at()->shouldBeLike(new \DateTime('@'.(self::FAKE_TIMESTAMP)));
    }

    function its_list_current_logs_returns_current_locks()
    {
        $query_result_data = array(
            'task_name'      => 'test',
            'lock_timestamp' => self::FAKE_TIMESTAMP,
            'timeout'        => 10,
            'lock_holder'    => self::FAKE_LOCK_HOLDER,
        );
        $lock_obj = $this->subject->build_lock_object($query_result_data);
        $this->subject->list_current_locks(array($query_result_data))->shouldBeLike(array($lock_obj));
    }

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     * @param string                                   $task_name
     * @param int                                      $lock_id
     */
    protected function givenOldLockToGarbageCollect($db_object, $task_name, $lock_id)
    {
        $db_object->fetch_all(self::SELECT_LOCK_SQL, array())
                  ->willReturn(array(array('task_name' => $task_name, 'lock_timestamp' => $lock_id, 'timeout' => 10, 'lock_holder' => self::FAKE_LOCK_HOLDER)));

        $db_object->execute(self::DELETE_LOCK_SQL, array(
            ':task_name'      => self::TASK_NAME,
            ':lock_timestamp' => self::FAKE_TIMESTAMP,
        ))->willReturn();
    }

}
