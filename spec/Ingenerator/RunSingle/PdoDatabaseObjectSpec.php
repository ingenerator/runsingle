<?php
/**
 * Defines PdoDatabaseObjectSpec - specifications for Ingenerator\RunSingle\PdoDatabaseObject
 *
 * @author     Matthias Gisder <matthias@ingenerator.com>
 * @copyright  2014 inGenerator Ltd
 * @licence    BSD
 */

namespace spec\Ingenerator\RunSingle;

use spec\ObjectBehavior;
use Prophecy\Argument;


use \PDO;

/**
 *
 * @see Ingenerator\RunSingle\PdoDatabaseObject
 */
class PdoDatabaseObjectSpec extends ObjectBehavior
{
    const FAKE_SQL = "SELECT * FROM locks WHERE task_name = :task_name";
    /**
     * Use $this->subject to get proper type hinting for the subject class
     * @var \Ingenerator\RunSingle\PdoDatabaseObject
     */
    protected $subject;

    /**
     * @var string
     */
    const TASK_NAME = 'testscript';
    const COMMAND   = 'testscript.sh';
    const TIMEOUT   = 10;

    /**
     * @param \PDO          $pdo
     * @param \PDOStatement $q
     */
    function let($pdo, $q)
    {
        $pdo->prepare(Argument::any())->willReturn($q);
        $pdo->query(Argument::type('string'))->willReturn();
        $pdo->setAttribute(Argument::any(), Argument::any())->willReturn();
        $this->subject->beConstructedWith($pdo, 'locks');
    }

    function it_is_initializable()
    {
        $this->subject->shouldHaveType('Ingenerator\RunSingle\PdoDatabaseObject');
    }

    /**
     * @param \PDO $pdo
     */
    function its_fetchall_prepares_query($pdo)
    {
        $this->subject->fetch_all(self::FAKE_SQL, array());
        $pdo->prepare(self::FAKE_SQL)->shouldHaveBeenCalled();
    }

    /**
     * @param \PDO          $pdo
     * @param \PDOStatement $q
     */
    function its_fetchall_binds_all_provided_params($pdo, $q)
    {
        $this->subject->fetch_all(self::FAKE_SQL, array(':foo' => 'foz', ':bar' => 'baz'));
        $q->bindParam(':foo', 'foz')->shouldHaveBeenCalled();
        $q->bindParam(':bar', 'baz')->shouldHaveBeenCalled();
    }

    /**
     * @param \PDOStatement $q
     */
    function its_fetchall_returns_fetchall_result($q)
    {
        $q->bindParam(':task_name', self::TASK_NAME)->willReturn();
        $q->bindParam(':command', self::COMMAND)->willReturn();
        $q->bindParam(':timeout', self::TIMEOUT)->willReturn();
        $q->execute(array(':task_name' => self::TASK_NAME, ':command' => self::COMMAND, ':timeout' => self::TIMEOUT))
          ->willReturn(array('testscript', 'testscript.sh', 10));
        $q->fetchAll()->willReturn(array('testscript', 'testscript.sh', 10));
        $this->subject->fetch_all(self::FAKE_SQL, array(
            ':task_name' => self::TASK_NAME,
            ':command'   => self::COMMAND,
            ':timeout'   => self::TIMEOUT
        ))->shouldBe(array('testscript', 'testscript.sh', 10));
    }

    /**
     * @param \PDO $pdo
     */
    function its_execute_prepares_query($pdo)
    {
        $this->subject->execute(self::FAKE_SQL, array(':task_name' => self::TASK_NAME));
        $pdo->prepare(self::FAKE_SQL)->shouldHaveBeenCalled();
    }

    /**
     * @param \PDO $pdo
     */
    function it_sets_error_mode($pdo)
    {
        $this->subject->get_db_table_name();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION)->shouldHaveBeenCalled();
    }

    /**
     * @param \PDOStatement $q
     */
    function its_execute_binds_all_provided_params($q)
    {
        $this->subject->execute(self::FAKE_SQL, array(':foo' => 'foz', ':bar' => 'baz'));
        $q->bindParam(':foo', 'foz')->shouldHaveBeenCalled();
        $q->bindParam(':bar', 'baz')->shouldHaveBeenCalled();
    }

    /**
     * @param \PDOStatement $q
     */
    function its_execute_executes_the_query($q)
    {
        $this->subject->execute(self::FAKE_SQL, array(':task_name' => self::TASK_NAME));
        $q->execute(array(':task_name' => self::TASK_NAME))->shouldHaveBeenCalled();
    }

}
