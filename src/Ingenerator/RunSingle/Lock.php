<?php
/**
 * Data object for locks.
 *
 * @author     Matthias Gisder <matthias@ingenerator.com>
 * @copyright  2014 inGenerator Ltd
 * @licence    BSD
 */


namespace Ingenerator\RunSingle;


class Lock
{

    const DATE_FORMAT = 'd/m/Y H:i:s';

    /**
     * @var string
     */
    protected $task_name;

    /**
     * @var string
     */
    protected $lock_id;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * @var string
     */
    protected $lock_holder;

    /**
     * @var \DateTime
     */
    protected $expires;

    /**
     * @var \DateTime
     */
    protected $locked_at;

    /**
     * @param mixed[] $data
     */
    public function __construct($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Return a description of the lock.
     *
     * @return string
     */
    public function __toString()
    {
        return \sprintf("Lock %s for task %s taken by %s at %s with timeout %s expires at %s.",
            $this->get_lock_id(),
            $this->get_task_name(),
            $this->get_lock_holder(),
            $this->get_locked_at()->format(self::DATE_FORMAT),
            $this->get_timeout(),
            $this->get_expires()->format(self::DATE_FORMAT)
        );
    }

    public function get_task_name()
    {
        return $this->task_name;
    }

    public function get_lock_id()
    {
        return $this->lock_id;
    }

    public function get_timeout()
    {
        return $this->timeout;
    }

    public function get_lock_holder()
    {
        return $this->lock_holder;
    }

    public function get_expires()
    {
        return $this->expires;
    }

    public function get_locked_at()
    {
        return $this->locked_at;
    }

}
