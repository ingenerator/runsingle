<?php

namespace Ingenerator\RunSingle;


class Lock {

    const DATE_FORMAT = 'd/m/Y H:i:s';

    protected $task_name;

    protected $lock_id;

    protected $timeout;

    protected $lock_holder;

    protected $expires;

    protected $locked_at;

    public function __construct($data)
    {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __toString()
    {
        return sprintf("Lock %s for task %s taken by %s at %s with timeout %s expires at %s.",
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
        return new \DateTime('@'.$this->expires);
    }

    public function get_locked_at()
    {
        return new \DateTime('@'.$this->locked_at);
    }

}
