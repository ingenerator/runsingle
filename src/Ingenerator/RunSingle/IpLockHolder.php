<?php

namespace Ingenerator\RunSingle;


class IpLockHolder implements LockHolder {

    protected $lock_holder;

    public function get_lock_holder()
    {
        if(!$this->lock_holder) {
            $host_name = gethostname();
            $ip = gethostbyname($host_name);

            $this->lock_holder = $ip;
        }
        return $this->lock_holder;
    }

}
