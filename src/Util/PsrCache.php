<?php

namespace Tpay\Util;

use Psr\SimpleCache\CacheInterface;

class PsrCache implements CacheInterface
{

    public function get($key, $default = null)
    {
        return unserialize(Cache::get($key, $default));
    }

    public function set($key, $value, $ttl = null)
    {
        Cache::set($key, serialize($value), (int)$ttl);
    }

    public function delete($key)
    {
        Cache::delete($key);
    }

    public function clear()
    {
        Cache::erase();
    }

    public function getMultiple($keys, $default = null)
    {
        $elements = [];
        foreach ($keys as $key) {
            $elements[] = $this->get($key, $default);
        }

        return $elements;
    }

    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
    }

    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    public function has($key)
    {
        return null !== $this->get($key);
    }
}
