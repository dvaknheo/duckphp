<?php
namespace DNMVCS\Core;

use ArrayAccess;

class HookChain implements ArrayAccess
{
    protected $chain = [];

    public function __invoke()
    {
        foreach ($this->chain as $v) {
            if (($v)()) {
                break;
            }
        }
    }

    public static function Hook(&$var, $callable, $append = true, $once = true)
    {
        if (is_null($var)) {
            $var = new static();
        }
        if ($var instanceof HookerInvoker) {
            $var->addHook($callable, $append, $once);
        } else {
            $var = $callable;
        }
    }

    public function add($callable, $append, $once)
    {
        if ($once && in_array($callable, $this->chain)) {
            return false;
        }
        $this->chain[] = $callable;
    }

    public function remove($callable)
    {
        $this->chain = array_filter($this->chain, function ($v, $k) use ($callable) {
            return $callable !== $v ? true : false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function has($callable)
    {
        return in_array($callable, $this->chain) ? true : false;
    }

    public function all()
    {
        return $this->chain;
    }

    //@override ArrayAccess
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->chain[] = $value;
        } else {
            $this->chain[$offset] = $value;
        }
    }

    //@override ArrayAccess
    public function offsetExists($offset)
    {
        return isset($this->chain[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->chain[$offset]);
    }

    //@override ArrayAccess
    public function offsetGet($offset)
    {
        return isset($this->chain[$offset]) ? $this->chain[$offset] : null;
    }
}
