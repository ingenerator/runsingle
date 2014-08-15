<?php

namespace Ingenerator\RunSingle;


class HostnameLockHolder implements LockHolder {

    protected $lock_holder;

    public function get_lock_holder()
    {
        if(!$this->lock_holder) {
            $this->lock_holder = gethostname();
        }
        return $this->lock_holder;
    }

}
